clear


echo "========================"
echo "= Commit Script Begins ="
echo "========================"

if [ ! $# = 1 ]
then 
    echo "[ERROR]incorrect parameters, syntax: $0 [component_name]"
    exit -1;
fi;

if [ -d "trunk" ]
then
    cd trunk
fi;
if [ ! -d "lib/components/mallocworks/$1" ]
then 
    echo "[ERROR]component $1 not found, case issue?"
    exit -1;
fi;

if [ ! -d "../reports" ]
then 
    echo "[status]creating reports directory..."
    mkdir ../reports
fi;

echo "[status]running phpunit config...";
./ci/phpunit.xml.sh $1 > ../phpunit.xml

echo "[status]running phpunit...";
if [ ! -f "../reports/phpunit.xml" ]
then 
    touch ../reports/phpunit.xml
fi;
if [ ! -f "../reports/pmd.xml" ]
then 
    touch ../reports/pmd.xml
fi;
if [ ! -d "../reports/clover" ]
then 
    mkdir ../reports/clover
fi;
if [ ! -f "../reports/clover/clover.xml" ]
then 
    touch ../reports/clover/clover.xml
fi;
phpunit --configuration ../phpunit.xml\
        --log-junit ../reports/phpunit.xml\
        --log-pmd ../reports/pmd.xml\
        --coverage-html ../reports/clover\
        --coverage-clover ../reports/clover/clover.xml\
        --testdox\
        --configuration ../phpunit.xml\
        lib/components/mallocworks/$1/tests/*Test.php

echo "[status]running phpcs...";
if [ ! -f "../reports/phpcs.xml" ]
then 
    touch ../reports/phpcs.xml
fi;
phpcs --standard=Yahoo\
      --extensions=php,inc\
      --report=checkstyle\
      --report-file=../reports/phpcs.xml\
      --ignore=**/tests/*Test.php\
      lib/components/mallocworks/$1

echo "======================"
echo "= Commit Script Ends ="
echo "======================"
