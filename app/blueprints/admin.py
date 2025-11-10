from flask import Blueprint, abort, flash, redirect, render_template, request, url_for
from flask_login import current_user, login_required

from ..extensions import db
from ..forms import ReviewDecisionForm
from ..models import AccessAgreement, AccessRequest, AccessRequestReview, Dataset, DatasetVersion, User


admin_bp = Blueprint("admin", __name__, url_prefix="/admin")


@admin_bp.before_request
def restrict_to_admins():
    if not current_user.is_authenticated:
        return redirect(url_for("auth.login", next=request.url))
    if current_user.role not in {"administrator", "reviewer"}:
        abort(403)


@admin_bp.route("/")
def dashboard():
    pending_requests = AccessRequest.query.filter(AccessRequest.status.in_(["submitted", "in_review"])).all()
    datasets = Dataset.query.order_by(Dataset.created_at.desc()).limit(10)
    users_pending = User.query.filter_by(status="pending").all()
    return render_template(
        "admin/dashboard.html",
        pending_requests=pending_requests,
        datasets=datasets,
        users_pending=users_pending,
    )


@admin_bp.route("/users/<int:user_id>/activate", methods=["POST"])
def activate_user(user_id):
    user = User.query.get_or_404(user_id)
    user.status = "active"
    db.session.commit()
    flash("Usuario activado", "success")
    return redirect(url_for("admin.dashboard"))


@admin_bp.route("/users/<int:user_id>/suspend", methods=["POST"])
def suspend_user(user_id):
    user = User.query.get_or_404(user_id)
    user.status = "suspended"
    db.session.commit()
    flash("Usuario suspendido", "info")
    return redirect(url_for("admin.dashboard"))


@admin_bp.route("/requests/<int:request_id>", methods=["GET", "POST"])
def review_request(request_id):
    access_request = AccessRequest.query.get_or_404(request_id)
    form = ReviewDecisionForm()
    if form.validate_on_submit():
        decision = form.decision.data
        access_request.status = "approved" if decision == "approved" else decision
        review = AccessRequestReview(
            access_request_id=access_request.id,
            reviewer_id=current_user.id,
            decision=decision,
            decision_notes=form.decision_notes.data,
        )
        db.session.add(review)

        if decision == "approved":
            agreement_text = f"Acceso aprobado al dataset {access_request.dataset.title}."
            agreement = access_request.agreement
            if agreement is None:
                agreement = AccessAgreement(
                    access_request_id=access_request.id,
                    dataset_id=access_request.dataset_id,
                    requester_id=access_request.requester_id,
                    agreement_text=agreement_text,
                )
                db.session.add(agreement)
            else:
                agreement.agreement_text = agreement_text

        db.session.commit()
        flash("Decisión registrada", "success")
        return redirect(url_for("admin.dashboard"))

    return render_template("admin/review_request.html", access_request=access_request, form=form)


@admin_bp.route("/datasets/<int:dataset_id>/versions")
def manage_versions(dataset_id):
    dataset = Dataset.query.get_or_404(dataset_id)
    versions = DatasetVersion.query.filter_by(dataset_id=dataset.id).order_by(DatasetVersion.uploaded_at.desc())
    return render_template("admin/manage_versions.html", dataset=dataset, versions=versions)
