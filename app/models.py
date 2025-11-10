from datetime import datetime
from uuid import uuid4

from flask_login import UserMixin
from werkzeug.security import check_password_hash, generate_password_hash

from .extensions import db


class User(db.Model, UserMixin):
    __tablename__ = "users"

    id = db.Column(db.BigInteger, primary_key=True)
    first_name = db.Column(db.String(100), nullable=False)
    last_name = db.Column(db.String(100), nullable=False)
    email = db.Column(db.String(190), unique=True, nullable=False)
    password_hash = db.Column(db.String(255), nullable=False)
    institution = db.Column(db.String(190))
    country = db.Column(db.String(100))
    role = db.Column(
        db.Enum("administrator", "reviewer", "internal_researcher", "external_researcher", name="role_enum"),
        nullable=False,
        default="external_researcher",
    )
    status = db.Column(
        db.Enum("pending", "active", "suspended", name="status_enum"),
        nullable=False,
        default="pending",
    )
    orcid = db.Column(db.String(32))
    phone_number = db.Column(db.String(30))
    created_at = db.Column(db.DateTime, default=datetime.utcnow)
    updated_at = db.Column(db.DateTime, default=datetime.utcnow, onupdate=datetime.utcnow)

    datasets = db.relationship("Dataset", back_populates="creator", lazy="dynamic")
    access_requests = db.relationship("AccessRequest", back_populates="requester", lazy="dynamic")

    def set_password(self, password: str) -> None:
        self.password_hash = generate_password_hash(password)

    def check_password(self, password: str) -> bool:
        return check_password_hash(self.password_hash, password)

    @property
    def is_active(self):
        from flask import current_app

        enforce_active = current_app.config.get("SECURITY_ENFORCE_ACTIVE_USERS", True)
        if not enforce_active:
            return True
        return self.status == "active"

    @property
    def full_name(self):
        return f"{self.first_name} {self.last_name}"


class Dataset(db.Model):
    __tablename__ = "datasets"

    id = db.Column(db.BigInteger, primary_key=True)
    slug = db.Column(db.String(160), unique=True, nullable=False)
    title = db.Column(db.String(255), nullable=False)
    summary = db.Column(db.Text, nullable=False)
    keywords = db.Column(db.Text)
    category = db.Column(db.String(120))
    geographic_scope = db.Column(db.String(120))
    publication_year = db.Column(db.Integer)
    contact_name = db.Column(db.String(190))
    contact_email = db.Column(db.String(190))
    legal_restrictions = db.Column(db.Text)
    visibility = db.Column(db.Enum("public", "internal", "restricted", name="visibility_enum"), default="restricted")
    storage_uri = db.Column(db.String(500), nullable=False)
    default_access_level = db.Column(db.Enum("preview", "download", name="access_level_enum"), default="preview")
    created_by = db.Column(db.BigInteger, db.ForeignKey("users.id"), nullable=False)
    created_at = db.Column(db.DateTime, default=datetime.utcnow)
    updated_at = db.Column(db.DateTime, default=datetime.utcnow, onupdate=datetime.utcnow)

    creator = db.relationship("User", back_populates="datasets")
    versions = db.relationship("DatasetVersion", back_populates="dataset", cascade="all, delete-orphan")

    def latest_version(self):
        return (
            DatasetVersion.query.filter_by(dataset_id=self.id)
            .order_by(DatasetVersion.uploaded_at.desc())
            .first()
        )


class DatasetVersion(db.Model):
    __tablename__ = "dataset_versions"

    id = db.Column(db.BigInteger, primary_key=True)
    dataset_id = db.Column(db.BigInteger, db.ForeignKey("datasets.id"), nullable=False)
    version_label = db.Column(db.String(50), nullable=False)
    file_name = db.Column(db.String(255), nullable=False)
    file_format = db.Column(db.Enum("csv", "dta", "rds", "zip", "other", name="dataset_format_enum"), nullable=False)
    file_size_bytes = db.Column(db.BigInteger)
    checksum = db.Column(db.String(128))
    storage_uri = db.Column(db.String(500), nullable=False)
    change_log = db.Column(db.Text)
    uploaded_by = db.Column(db.BigInteger, db.ForeignKey("users.id"), nullable=False)
    uploaded_at = db.Column(db.DateTime, default=datetime.utcnow)

    dataset = db.relationship("Dataset", back_populates="versions")
    files = db.relationship("DatasetFile", back_populates="dataset_version", cascade="all, delete-orphan")
    uploader = db.relationship("User")


class DatasetFile(db.Model):
    __tablename__ = "dataset_files"

    id = db.Column(db.BigInteger, primary_key=True)
    dataset_version_id = db.Column(db.BigInteger, db.ForeignKey("dataset_versions.id"), nullable=False)
    file_label = db.Column(db.String(150), nullable=False)
    file_format = db.Column(db.Enum("csv", "dta", "rds", "documentation", "codebook", "other", name="dataset_file_format_enum"), nullable=False)
    storage_uri = db.Column(db.String(500), nullable=False)
    file_size_bytes = db.Column(db.BigInteger)
    checksum = db.Column(db.String(128))
    created_at = db.Column(db.DateTime, default=datetime.utcnow)

    dataset_version = db.relationship("DatasetVersion", back_populates="files")


class AccessRequest(db.Model):
    __tablename__ = "access_requests"

    id = db.Column(db.BigInteger, primary_key=True)
    dataset_id = db.Column(db.BigInteger, db.ForeignKey("datasets.id"), nullable=False)
    requester_id = db.Column(db.BigInteger, db.ForeignKey("users.id"), nullable=False)
    intended_use = db.Column(db.Text, nullable=False)
    methodology = db.Column(db.Text)
    institution = db.Column(db.String(190))
    expected_publication = db.Column(db.Text)
    safeguards = db.Column(db.Text)
    agreement_version = db.Column(db.String(50))
    status = db.Column(
        db.Enum(
            "submitted",
            "in_review",
            "approved",
            "rejected",
            "needs_more_info",
            "revoked",
            name="access_status_enum",
        ),
        default="submitted",
        nullable=False,
    )
    submitted_at = db.Column(db.DateTime, default=datetime.utcnow)
    last_status_change = db.Column(db.DateTime, default=datetime.utcnow, onupdate=datetime.utcnow)

    dataset = db.relationship("Dataset")
    requester = db.relationship("User", back_populates="access_requests")
    reviews = db.relationship("AccessRequestReview", back_populates="access_request", cascade="all, delete-orphan")
    agreement = db.relationship("AccessAgreement", back_populates="access_request", uselist=False)


class AccessRequestReview(db.Model):
    __tablename__ = "access_request_reviews"

    id = db.Column(db.BigInteger, primary_key=True)
    access_request_id = db.Column(db.BigInteger, db.ForeignKey("access_requests.id"), nullable=False)
    reviewer_id = db.Column(db.BigInteger, db.ForeignKey("users.id"), nullable=False)
    decision = db.Column(db.Enum("approved", "rejected", "needs_more_info", name="review_decision_enum"), nullable=False)
    decision_notes = db.Column(db.Text)
    decided_at = db.Column(db.DateTime, default=datetime.utcnow)

    access_request = db.relationship("AccessRequest", back_populates="reviews")
    reviewer = db.relationship("User")


class AccessAgreement(db.Model):
    __tablename__ = "access_agreements"

    id = db.Column(db.BigInteger, primary_key=True)
    access_request_id = db.Column(db.BigInteger, db.ForeignKey("access_requests.id"), nullable=False)
    dataset_id = db.Column(db.BigInteger, db.ForeignKey("datasets.id"), nullable=False)
    requester_id = db.Column(db.BigInteger, db.ForeignKey("users.id"), nullable=False)
    agreement_text = db.Column(db.Text, nullable=False)
    agreement_signed_at = db.Column(db.DateTime, default=datetime.utcnow)
    signature_ip = db.Column(db.String(45))

    access_request = db.relationship("AccessRequest", back_populates="agreement")
    dataset = db.relationship("Dataset")
    requester = db.relationship("User")


class DownloadAuditLog(db.Model):
    __tablename__ = "download_audit_logs"

    id = db.Column(db.BigInteger, primary_key=True)
    dataset_version_id = db.Column(db.BigInteger, db.ForeignKey("dataset_versions.id"), nullable=False)
    access_request_id = db.Column(db.BigInteger, db.ForeignKey("access_requests.id"))
    user_id = db.Column(db.BigInteger, db.ForeignKey("users.id"), nullable=False)
    download_token = db.Column(db.String(120), nullable=False, unique=True, default=lambda: uuid4().hex)
    ip_address = db.Column(db.String(45))
    user_agent = db.Column(db.String(255))
    downloaded_at = db.Column(db.DateTime, default=datetime.utcnow)

    dataset_version = db.relationship("DatasetVersion")
    access_request = db.relationship("AccessRequest")
    user = db.relationship("User")


class NotificationTemplate(db.Model):
    __tablename__ = "notification_templates"

    id = db.Column(db.BigInteger, primary_key=True)
    code = db.Column(db.String(80), unique=True, nullable=False)
    subject = db.Column(db.String(190), nullable=False)
    body = db.Column(db.Text, nullable=False)
    locale = db.Column(db.String(10), default="es", nullable=False)
    created_at = db.Column(db.DateTime, default=datetime.utcnow)
    updated_at = db.Column(db.DateTime, default=datetime.utcnow, onupdate=datetime.utcnow)


class SystemSetting(db.Model):
    __tablename__ = "system_settings"

    id = db.Column(db.BigInteger, primary_key=True)
    setting_key = db.Column(db.String(120), unique=True, nullable=False)
    setting_value = db.Column(db.Text, nullable=False)
    updated_at = db.Column(db.DateTime, default=datetime.utcnow, onupdate=datetime.utcnow)


class PasswordReset(db.Model):
    __tablename__ = "password_resets"

    id = db.Column(db.BigInteger, primary_key=True)
    user_id = db.Column(db.BigInteger, db.ForeignKey("users.id"), nullable=False)
    token = db.Column(db.String(120), unique=True, nullable=False)
    expires_at = db.Column(db.DateTime, nullable=False)
    created_at = db.Column(db.DateTime, default=datetime.utcnow)

    user = db.relationship("User")


class ApiToken(db.Model):
    __tablename__ = "api_tokens"

    id = db.Column(db.BigInteger, primary_key=True)
    user_id = db.Column(db.BigInteger, db.ForeignKey("users.id"), nullable=False)
    token = db.Column(db.String(120), unique=True, nullable=False)
    description = db.Column(db.String(190))
    expires_at = db.Column(db.DateTime)
    created_at = db.Column(db.DateTime, default=datetime.utcnow)
    last_used_at = db.Column(db.DateTime)

    user = db.relationship("User")
