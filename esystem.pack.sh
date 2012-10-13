#!/bin/sh

ver=$(cat version)
tpp=$$

tmp=/tmp/eSys.${tpp}

mkdir -p ${tmp}/eSystem-${ver}
cp -rf eSystem/ eSystem.php ${tmp}/eSystem-${ver}/
cp -rf package.xml ${tmp}/

pushd ${tmp}
find ./ -name CVS -exec rm -rf {} \;
tar cvfpz eSystem-${ver}.tgz eSystem-${ver} package.xml
popd

mv ${tmp}/eSystem-${ver}.tgz ./
rm -rf ${tmp}
