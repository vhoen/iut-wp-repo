#!/usr/bin/env bash
# Bump plugin versions (major / minor / patch) and build installable zips.
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
DIST_DIR="${ROOT_DIR}/dist"

usage() {
	cat <<'EOF'
Usage: ./scripts/create-release.sh

Interactively choose a SemVer bump (patch / minor / major), update every
wp-* plugin bootstrap Version header and *_VERSION define, then write
WordPress-installable zip archives under dist/.
EOF
}

if [[ "${1:-}" == "-h" || "${1:-}" == "--help" ]]; then
	usage
	exit 0
fi

bump_semver() {
	local version="$1"
	local kind="$2"
	local major minor patch

	if [[ ! "$version" =~ ^([0-9]+)\.([0-9]+)\.([0-9]+)$ ]]; then
		echo "Version invalide (attendu X.Y.Z) : ${version}" >&2
		return 1
	fi

	major="${BASH_REMATCH[1]}"
	minor="${BASH_REMATCH[2]}"
	patch="${BASH_REMATCH[3]}"

	case "$kind" in
		major)
			major=$((major + 1))
			minor=0
			patch=0
			;;
		minor)
			minor=$((minor + 1))
			patch=0
			;;
		patch)
			patch=$((patch + 1))
			;;
		*)
			echo "Type de bump inconnu : ${kind}" >&2
			return 1
			;;
	esac

	echo "${major}.${minor}.${patch}"
}

find_plugin_bootstrap() {
	local plugin_dir="$1"
	local file

	for file in "${plugin_dir}"/*.php; do
		[[ -f "$file" ]] || continue
		if grep -q 'Plugin Name:' "$file"; then
			echo "$file"
			return 0
		fi
	done

	return 1
}

read_plugin_version() {
	local bootstrap="$1"
	local version

	version="$(grep -E '^[[:space:]]*\*[[:space:]]*Version:[[:space:]]*' "$bootstrap" | head -n1 | sed -E 's/^[[:space:]]*\*[[:space:]]*Version:[[:space:]]*//')"
	version="${version%%[[:space:]]*}"

	if [[ -z "$version" ]]; then
		echo "Impossible de lire Version: dans ${bootstrap}" >&2
		return 1
	fi

	echo "$version"
}

update_plugin_version() {
	local bootstrap="$1"
	local old_version="$2"
	local new_version="$3"

	# Plugin header: * Version:           X.Y.Z
	sed -i -E "s/^([[:space:]]*\*[[:space:]]*Version:[[:space:]]*)${old_version}([[:space:]]*)$/\1${new_version}\2/" "$bootstrap"

	# Constant: define( 'FOO_VERSION', 'X.Y.Z' );
	sed -i -E "s/(define\(\s*'[A-Z0-9_]+_VERSION',\s*')${old_version}('\s*\);)/\1${new_version}\2/" "$bootstrap"

	if ! grep -qE "Version:[[:space:]]*${new_version}" "$bootstrap"; then
		echo "Échec de la mise à jour du header Version dans ${bootstrap}" >&2
		return 1
	fi
	if ! grep -qE "define\(\s*'[A-Z0-9_]+_VERSION',\s*'${new_version}'" "$bootstrap"; then
		echo "Échec de la mise à jour de la constante *_VERSION dans ${bootstrap}" >&2
		return 1
	fi
}

build_plugin_zip() {
	local plugin_slug="$1"
	local version="$2"
	local zip_path="${DIST_DIR}/${plugin_slug}-${version}.zip"

	rm -f "$zip_path"
	(
		cd "$ROOT_DIR"
		zip -r "$zip_path" "$plugin_slug" \
			-x "${plugin_slug}/.*" \
			-x "${plugin_slug}/**/.*" \
			>/dev/null
	)

	echo "$zip_path"
}

echo "=== create-release ==="
echo "Repo : ${ROOT_DIR}"
echo
echo "Type de mise à jour ?"
echo "  1) patch   (1.0.0 → 1.0.1)"
echo "  2) minor   (1.0.0 → 1.1.0)"
echo "  3) major   (1.0.0 → 2.0.0)"
echo
read -r -p "Choix [1/2/3] : " choice

case "$choice" in
	1 | patch | p) bump_kind="patch" ;;
	2 | minor | m) bump_kind="minor" ;;
	3 | major | M) bump_kind="major" ;;
	*)
		echo "Choix invalide." >&2
		exit 1
		;;
esac

mapfile -t plugin_dirs < <(
	find "$ROOT_DIR" -mindepth 1 -maxdepth 1 -type d -name 'wp-*' | sort
)

if [[ ${#plugin_dirs[@]} -eq 0 ]]; then
	echo "Aucun plugin wp-* trouvé." >&2
	exit 1
fi

declare -a plan_slugs=()
declare -a plan_bootstraps=()
declare -a plan_old=()
declare -a plan_new=()

for plugin_dir in "${plugin_dirs[@]}"; do
	slug="$(basename "$plugin_dir")"
	bootstrap="$(find_plugin_bootstrap "$plugin_dir")" || {
		echo "Bootstrap introuvable pour ${slug}, ignoré." >&2
		continue
	}
	old_version="$(read_plugin_version "$bootstrap")"
	new_version="$(bump_semver "$old_version" "$bump_kind")"
	plan_slugs+=("$slug")
	plan_bootstraps+=("$bootstrap")
	plan_old+=("$old_version")
	plan_new+=("$new_version")
done

if [[ ${#plan_slugs[@]} -eq 0 ]]; then
	echo "Aucun plugin valide à publier." >&2
	exit 1
fi

echo
echo "Bump demandé : ${bump_kind}"
echo "Plugins :"
for i in "${!plan_slugs[@]}"; do
	echo "  - ${plan_slugs[$i]} : ${plan_old[$i]} → ${plan_new[$i]}"
done
echo
read -r -p "Confirmer et générer les zips ? [y/N] : " confirm
case "$confirm" in
	y | Y | yes | YES) ;;
	*)
		echo "Annulé."
		exit 0
		;;
esac

mkdir -p "$DIST_DIR"

echo
for i in "${!plan_slugs[@]}"; do
	slug="${plan_slugs[$i]}"
	bootstrap="${plan_bootstraps[$i]}"
	old_version="${plan_old[$i]}"
	new_version="${plan_new[$i]}"

	echo "→ ${slug} : bump ${old_version} → ${new_version}"
	update_plugin_version "$bootstrap" "$old_version" "$new_version"
	zip_path="$(build_plugin_zip "$slug" "$new_version")"
	echo "  zip : ${zip_path}"
done

echo
echo "Terminé. Archives dans : ${DIST_DIR}"
