TAG=`cat ./src/version.txt`
echo $TAG ;
/data/x/tools/mara-pub/rocket_pub.sh  --prj hydra --env online --tag $TAG --host $*
