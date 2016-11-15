TAG=`cat ./src/version.txt`
echo $TAG ;
cd $HOME/devspace/mara-pub ;
/data/x/tools/mara-pub/rocket_pub.sh  --prj hydra  --tag $TAG --host $*
