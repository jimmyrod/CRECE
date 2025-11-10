import re
import unicodedata


_slugify_re = re.compile(r"[^a-z0-9]+")


def slugify(text: str) -> str:
    text = (
        unicodedata.normalize("NFKD", text)
        .encode("ascii", "ignore")
        .decode("ascii")
        .lower()
        .strip()
    )
    text = _slugify_re.sub("-", text)
    return text.strip("-")
