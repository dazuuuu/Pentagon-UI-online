#!/usr/bin/env python3
"""Fix broken static HTML paths without changing UI markup/structure.

- Absolute site hrefs (/destinations/...) -> depth-relative paths
- Broken protocol-relative hosts turned into ../host -> https://host
- Corrupted "https://Pentagon Quest.com/..." and bonfireadventures.com
  wp-content / wp-includes asset URLs -> local relative paths
"""
from __future__ import annotations

import re
from pathlib import Path
from urllib.parse import unquote

PUBLIC = Path(__file__).resolve().parents[1] / "public"
SKIP_PREFIXES = ("wp-content/plugins/", "wp-content/litespeed/", "wp-includes/")

ABS_HREF_RE = re.compile(r'\bhref=(["\'])/(?!/)([^"\']*)\1')
BROKEN_PROTO_RE = re.compile(
    r'(?:(?:\.\./)+)(static\.zdassets\.com|bonfire\.virtualkrew\.com)([^"\'\s>]*)'
)
# Domain with accidental space from naive brand replace
PQ_SPACE_RE = re.compile(
    r'https?://Pentagon%20Quest\.com|https?://Pentagon Quest\.com',
    re.IGNORECASE,
)
BONFIRE_ASSET_RE = re.compile(
    r'https?://(?:www\.)?bonfireadventures\.com/(wp-content|wp-includes)/([^"\'\s)]*)',
    re.IGNORECASE,
)
PQ_ASSET_RE = re.compile(
    r'https?://(?:www\.)?pentagonquest\.com/(wp-content|wp-includes)/([^"\'\s)]*)',
    re.IGNORECASE,
)
PQ_SPACE_ASSET_RE = re.compile(
    r'https?://Pentagon(?:%20| )Quest\.com/(wp-content|wp-includes)/([^"\'\s)]*)',
    re.IGNORECASE,
)


def depth_prefix(html_file: Path) -> str:
    rel = html_file.relative_to(PUBLIC)
    depth = len(rel.parts) - 1
    return "../" * depth


def to_relative_site_path(path: str, prefix: str) -> str:
    path = unquote(path.split("#")[0].split("?")[0]).strip("/")
    frag = ""
    # preserve hash/query from original via caller
    if path == "":
        return prefix + "index.html"
    # Prefer index.html for directory-style links
    local = PUBLIC / path
    if local.is_dir() or (PUBLIC / path / "index.html").exists():
        return prefix + path.strip("/") + "/index.html"
    if (PUBLIC / path).exists():
        return prefix + path
    # Fallback: keep path as folder index
    return prefix + path.strip("/") + ("/index.html" if "." not in Path(path).name else "")


def fix_file(html_file: Path) -> bool:
    rel = str(html_file.relative_to(PUBLIC)).replace("\\", "/")
    if rel.startswith(SKIP_PREFIXES):
        return False

    text = html_file.read_text(encoding="utf-8", errors="ignore")
    orig = text
    prefix = depth_prefix(html_file)

    def repl_abs(m: re.Match) -> str:
        quote, path = m.group(1), m.group(2)
        # Keep query/hash
        q = ""
        h = ""
        if "?" in path:
            path, qrest = path.split("?", 1)
            q = "?" + qrest
        if "#" in path:
            path, hrest = path.split("#", 1)
            h = "#" + hrest
        elif "#" in q:
            # already in q
            pass
        if "#" in q and h == "":
            pass
        # hash may be after query in original; path already stripped above for ?
        # Re-parse more carefully
        raw = m.group(2)
        path_only = raw
        suffix = ""
        for sep in ("#", "?"):
            if sep in path_only:
                # keep first occurrence of either in original order
                pass
        # Split keeping order of ? and #
        mqh = re.match(r'^([^?#]*)(\?[^#]*)?(#.*)?$', raw)
        path_only = mqh.group(1) if mqh else raw
        suffix = (mqh.group(2) or "") + (mqh.group(3) or "") if mqh else ""
        return f'href={quote}{to_relative_site_path(path_only, prefix)}{suffix}{quote}'

    text = ABS_HREF_RE.sub(repl_abs, text)
    text = BROKEN_PROTO_RE.sub(r'https://\1\2', text)

    def repl_asset(m: re.Match) -> str:
        folder, rest = m.group(1), m.group(2)
        return f"{prefix}{folder}/{rest}"

    text = PQ_SPACE_ASSET_RE.sub(repl_asset, text)
    text = BONFIRE_ASSET_RE.sub(repl_asset, text)
    text = PQ_ASSET_RE.sub(repl_asset, text)

    # Remaining "Pentagon Quest.com" non-asset URLs -> local home-relative
    def repl_pq_space(m: re.Match) -> str:
        return prefix.rstrip("/") + "/" if prefix else "./"

    # Only replace bare domain leftovers in strings (not already handled assets)
    text = re.sub(
        r'https?://Pentagon(?:%20| )Quest\.com/?',
        lambda m: prefix + "index.html" if prefix else "index.html",
        text,
        flags=re.IGNORECASE,
    )

    if text != orig:
        html_file.write_text(text, encoding="utf-8")
        return True
    return False


def main() -> None:
    changed = 0
    for f in PUBLIC.rglob("*.html"):
        if fix_file(f):
            changed += 1
            print("fixed", f.relative_to(PUBLIC))
    print(f"Done. Updated {changed} HTML files.")


if __name__ == "__main__":
    main()
