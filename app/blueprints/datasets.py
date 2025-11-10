import os
from pathlib import Path
from uuid import uuid4

from flask import (
    Blueprint,
    abort,
    current_app,
    flash,
    redirect,
    render_template,
    request,
    send_file,
    url_for,
)
from flask_login import current_user, login_required

from ..extensions import db
from ..forms import AccessRequestForm, DatasetForm, DatasetVersionForm, DocumentationUploadForm
from ..models import AccessRequest, Dataset, DatasetFile, DatasetVersion, DownloadAuditLog
from ..utils.access import user_can_download_version, user_can_view_dataset
from ..utils.files import compute_checksum, save_upload
from ..utils.slugify import slugify


datasets_bp = Blueprint("datasets", __name__)


@datasets_bp.route("/")
def index():
    visibility_filter = ["public"]
    if current_user.is_authenticated and current_user.role in {"administrator", "reviewer", "internal_researcher"}:
        visibility_filter.extend(["internal", "restricted"])
    datasets = Dataset.query.filter(Dataset.visibility.in_(visibility_filter)).order_by(Dataset.created_at.desc())
    return render_template("datasets/index.html", datasets=datasets)


@datasets_bp.route("/datasets/new", methods=["GET", "POST"])
@login_required
def create_dataset():
    if current_user.role not in {"administrator", "internal_researcher"}:
        abort(403)

    form = DatasetForm()
    if form.validate_on_submit():
        slug = slugify(form.slug.data)
        if Dataset.query.filter_by(slug=slug).first():
            form.slug.errors.append("Ya existe un dataset con ese identificador")
        else:
            dataset = Dataset(
                slug=slug,
                title=form.title.data,
                summary=form.summary.data,
                keywords=form.keywords.data,
                category=form.category.data,
                geographic_scope=form.geographic_scope.data,
                publication_year=int(form.publication_year.data) if form.publication_year.data else None,
                contact_name=form.contact_name.data,
                contact_email=form.contact_email.data,
                legal_restrictions=form.legal_restrictions.data,
                visibility=form.visibility.data,
                default_access_level=form.default_access_level.data,
                storage_uri=f"dataset://{slug}",
                created_by=current_user.id,
            )
            db.session.add(dataset)
            db.session.commit()
            flash("Dataset creado. Ahora puedes subir una versión.", "success")
            return redirect(url_for("datasets.upload_version", dataset_id=dataset.id))
    return render_template("datasets/create_dataset.html", form=form)


@datasets_bp.route("/datasets/<int:dataset_id>/upload", methods=["GET", "POST"])
@login_required
def upload_version(dataset_id):
    dataset = Dataset.query.get_or_404(dataset_id)
    if current_user.role not in {"administrator", "internal_researcher"}:
        abort(403)

    version_form = DatasetVersionForm()
    doc_form = DocumentationUploadForm()

    if version_form.validate_on_submit() and "submit_version" in request.form:
        file_storage = version_form.file.data
        saved_path = save_upload(
            file_storage,
            base_path=current_app.config["UPLOAD_FOLDER"],
            subdir=Path("datasets") / dataset.slug / version_form.version_label.data,
        )
        checksum = compute_checksum(saved_path)
        storage_uri = str(saved_path.relative_to(current_app.config["UPLOAD_FOLDER"]))
        version = DatasetVersion(
            dataset_id=dataset.id,
            version_label=version_form.version_label.data,
            file_name=file_storage.filename,
            file_format=version_form.file_format.data,
            file_size_bytes=os.path.getsize(saved_path),
            checksum=checksum,
            storage_uri=storage_uri,
            change_log=version_form.change_log.data,
            uploaded_by=current_user.id,
        )
        db.session.add(version)
        db.session.commit()
        flash("Versión subida correctamente", "success")
        return redirect(url_for("datasets.detail", slug=dataset.slug))

    if doc_form.validate_on_submit() and "submit_doc" in request.form:
        latest_version = dataset.latest_version()
        if not latest_version:
            doc_form.file_label.errors.append("Primero sube una versión principal del dataset")
        else:
            file_storage = doc_form.file.data
            saved_path = save_upload(
                file_storage,
                base_path=current_app.config["UPLOAD_FOLDER"],
                subdir=Path("datasets") / dataset.slug / "docs",
            )
            checksum = compute_checksum(saved_path)
            storage_uri = str(saved_path.relative_to(current_app.config["UPLOAD_FOLDER"]))
            documentation = DatasetFile(
                dataset_version_id=latest_version.id,
                file_label=doc_form.file_label.data,
                file_format=doc_form.file_format.data,
                file_size_bytes=os.path.getsize(saved_path),
                checksum=checksum,
                storage_uri=storage_uri,
            )
            db.session.add(documentation)
            db.session.commit()
            flash("Archivo adicional guardado", "success")
            return redirect(url_for("datasets.detail", slug=dataset.slug))

    return render_template(
        "datasets/upload_version.html",
        dataset=dataset,
        version_form=version_form,
        doc_form=doc_form,
    )


@datasets_bp.route("/datasets/<slug>")
def detail(slug):
    dataset = Dataset.query.filter_by(slug=slug).first_or_404()
    if not user_can_view_dataset(current_user, dataset):
        abort(403)

    request_form = AccessRequestForm()
    return render_template(
        "datasets/detail.html",
        dataset=dataset,
        request_form=request_form,
    )


@datasets_bp.route("/datasets/<slug>/request", methods=["POST"])
@login_required
def request_access(slug):
    dataset = Dataset.query.filter_by(slug=slug).first_or_404()
    if not user_can_view_dataset(current_user, dataset):
        abort(403)

    form = AccessRequestForm()
    if form.validate_on_submit():
        access_request = AccessRequest(
            dataset_id=dataset.id,
            requester_id=current_user.id,
            intended_use=form.intended_use.data,
            methodology=form.methodology.data,
            institution=form.institution.data or current_user.institution,
            expected_publication=form.expected_publication.data,
            safeguards=form.safeguards.data,
            agreement_version=form.agreement_version.data,
            status="submitted",
        )
        db.session.add(access_request)
        db.session.commit()
        flash("Tu solicitud ha sido enviada y será revisada.", "success")
    else:
        flash("Revisa el formulario e inténtalo nuevamente.", "danger")
    return redirect(url_for("datasets.detail", slug=slug))


@datasets_bp.route("/downloads/<int:version_id>")
@login_required
def download_version(version_id):
    version = DatasetVersion.query.get_or_404(version_id)
    dataset = version.dataset
    if not user_can_view_dataset(current_user, dataset):
        abort(403)

    if not user_can_download_version(current_user, version):
        flash("Necesitas una solicitud aprobada para descargar este dataset.", "warning")
        return redirect(url_for("datasets.detail", slug=dataset.slug))

    storage_path = Path(current_app.config["UPLOAD_FOLDER"]) / version.storage_uri
    if not storage_path.exists():
        abort(404)

    access_request = (
        AccessRequest.query.filter_by(dataset_id=dataset.id, requester_id=current_user.id, status="approved")
        .order_by(AccessRequest.last_status_change.desc())
        .first()
    )

    log_entry = DownloadAuditLog(
        dataset_version_id=version.id,
        access_request_id=access_request.id if access_request else None,
        user_id=current_user.id,
        ip_address=request.remote_addr,
        user_agent=request.headers.get("User-Agent"),
    )
    db.session.add(log_entry)
    db.session.commit()

    return send_file(storage_path, as_attachment=True, download_name=version.file_name)
