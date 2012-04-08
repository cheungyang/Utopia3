if [ ! $# = 1 ]
then 
    echo \"[ERROR]incorrect parameters, syntax: $0 [component_name]\"
    exit -1;
fi;
echo "<phpunit verbose=\"true\"
         convertNoticesToExceptions=\"false\"
         convertWarningsToExceptions=\"false\">
    <testsuite name=\"Utopia3 - Component $1 Tests\">
        <directory suffix=\"Test.php\">lib/components/mallocworks/$1/tests</directory>
    </testsuite>
          
    <filter>
        <whitelist>
              <directory suffix=\".php\">lib/components/mallocworks/$1/</directory>
              <directory suffix=\".inc\">lib/components/mallocworks/$1/</directory>        
              <exclude>
                  <directory suffix=\"Test.php\">lib/components/mallocworks/$1/</directory>
              </exclude>
        </whitelist>
      </filter>
      
    <logging>
        <log type=\"coverage-html\" target=\"../reports/clover\" charset=\"UTF-8\"
            yui=\"true\" highlight=\"false\"
             lowUpperBound=\"35\" highLowerBound=\"70\"/>
        <log type=\"coverage-xml\" target=\"../reports/clover/clover.xml\"/>
        <log type=\"graphviz\" target=\"../reports/logfile.dot\"/>
        <log type=\"metrics-xml\" target=\"../reports/metrics.xml\"/>
        <log type=\"pmd-xml\" target=\"../reports/pmd.xml\" cpdMinLines=\"5\" cpdMinMatches=\"70\"/>
    </logging>
</phpunit>"