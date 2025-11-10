from flask_login import AnonymousUserMixin

from ..models import AccessRequest, Dataset, DatasetVersion


def user_can_view_dataset(user, dataset: Dataset) -> bool:
    if isinstance(user, AnonymousUserMixin):
        return dataset.visibility == "public"

    if user.role in {"administrator", "reviewer"}:
        return True

    if dataset.visibility == "public":
        return True

    if dataset.visibility == "internal" and user.role in {"internal_researcher"}:
        return True

    if dataset.visibility == "restricted" and user.role in {"internal_researcher"}:
        return True

    if dataset.visibility == "restricted" and dataset.created_by == user.id:
        return True

    return False


def user_can_download_version(user, version: DatasetVersion) -> bool:
    dataset = version.dataset
    if not user_can_view_dataset(user, dataset):
        return False

    if dataset.default_access_level == "download" and dataset.visibility == "public":
        return True

    if isinstance(user, AnonymousUserMixin):
        return False

    if user.role in {"administrator", "reviewer"}:
        return True

    approved_request = (
        AccessRequest.query.filter_by(dataset_id=dataset.id, requester_id=user.id, status="approved")
        .order_by(AccessRequest.last_status_change.desc())
        .first()
    )
    return approved_request is not None
