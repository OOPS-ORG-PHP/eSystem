#!/bin/sh

ver=$(cat version)
tpp=$$

tmp=/tmp/eSys.${tpp}

mkdir -p ${tmp}/eSystem-${ver}
cp -rf eSystem/ eSystem.php ${tmp}/eSystem-${ver}/
cp -rf package.xml ${tmp}/

pushd ${tmp}/eSystem-${ver}
	list=$(grep "md5sum" ../package.xml | sed 's/.*"@\|@".*//g')

	for i in $list
	do
		md5s=$(md5sum $i | awk '{print $1}')
		perl -pi -e "s!\@${i}\@!${md5s}!g" ../package.xml
	done
popd

pushd ${tmp}
	find ./ -name CVS -exec rm -rf {} \;
	tar cvfpz eSystem-${ver}.tgz eSystem-${ver} package.xml
popd

mv ${tmp}/eSystem-${ver}.tgz ./
rm -rf ${tmp}
