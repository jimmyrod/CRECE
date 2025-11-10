import hashlib
from pathlib import Path

from werkzeug.datastructures import FileStorage
from werkzeug.utils import secure_filename


def save_upload(file_storage: FileStorage, base_path: str, subdir: Path) -> Path:
    secure_name = secure_filename(file_storage.filename)
    destination_dir = Path(base_path) / subdir
    destination_dir.mkdir(parents=True, exist_ok=True)
    destination_path = destination_dir / secure_name
    file_storage.save(destination_path)
    return destination_path


def compute_checksum(file_path: Path, algorithm: str = "sha256") -> str:
    hash_obj = hashlib.new(algorithm)
    with open(file_path, "rb") as f:
        for chunk in iter(lambda: f.read(8192), b""):
            hash_obj.update(chunk)
    return hash_obj.hexdigest()
