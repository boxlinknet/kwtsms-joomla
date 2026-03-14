#!/usr/bin/env bash
set -euo pipefail

VERSION="1.2.0"
BUILD_DIR="build"
SRC_DIR="src"

echo "Building pkg_kwtsms v${VERSION}..."

# Clean build directory
rm -rf "${BUILD_DIR}"
mkdir -p "${BUILD_DIR}/pkg_kwtsms/constituents"

# Build each extension ZIP from src/
for ext in com_kwtsms plg_system_kwtsms plg_task_kwtsms plg_vmcustom_kwtsms; do
    echo "  Packaging ${ext}..."
    (cd "${SRC_DIR}/${ext}" && zip -r "../../${BUILD_DIR}/pkg_kwtsms/constituents/${ext}.zip" . \
        --exclude "*.DS_Store" \
        --exclude "__MACOSX/*" \
        --exclude ".gitkeep")
done

# Copy package manifest and language files into pkg dir
cp "${SRC_DIR}/pkg_kwtsms.xml" "${BUILD_DIR}/pkg_kwtsms/"
cp -r "${SRC_DIR}/language" "${BUILD_DIR}/pkg_kwtsms/"

# Build final package ZIP
(cd "${BUILD_DIR}" && zip -r "pkg_kwtsms-${VERSION}.zip" pkg_kwtsms/)

echo "Build complete: ${BUILD_DIR}/pkg_kwtsms-${VERSION}.zip"
ls -lh "${BUILD_DIR}/pkg_kwtsms-${VERSION}.zip"
