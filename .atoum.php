<?php

/*
This file will automatically be included before EACH run.

Use it to configure atoum or anything that needs to be done before EACH run.

More information on documentation:
[en] http://docs.atoum.org/en/latest/chapter3.html#configuration-files
[fr] http://docs.atoum.org/fr/latest/lancement_des_tests.html#fichier-de-configuration
*/
use mageekguy\atoum\reports;
use mageekguy\atoum\reports\coverage;
use mageekguy\atoum\writers\std;
use \mageekguy\atoum;

$report = $script->addDefaultReport();

/*
LOGO

// This will add the atoum logo before each run.
$report->addField(new atoum\report\fields\runner\atoum\logo());

// This will add a green or red logo after each run depending on its status.
$report->addField(new atoum\report\fields\runner\result\logo());
*/

/*
CODE COVERAGE SETUP

// Please replace in next line "Project Name" by your project name and "/path/to/destination/directory" by your destination directory path for html files.
$coverageField = new atoum\report\fields\runner\coverage\html('Project Name', '/path/to/destination/directory');

// Please replace in next line http://url/of/web/site by the root url of your code coverage web site.
$coverageField->setRootUrl('http://url/of/web/site');

$report->addField($coverageField);
*/

/*
TEST EXECUTION SETUP

// Please replace in next line "/path/to/your/tests/units/classes/directory" by your unit test's directory.
$runner->addTestsFromDirectory('path/to/your/tests/units/classes/directory');
*/

/*
TEST GENERATOR SETUP

$testGenerator = new atoum\test\generator();

// Please replace in next line "/path/to/your/tests/units/classes/directory" by your unit test's directory.
$testGenerator->setTestClassesDirectory('path/to/your/tests/units/classes/directory');

// Please replace in next line "your\project\namespace\tests\units" by your unit test's namespace.
$testGenerator->setTestClassNamespace('your\project\namespace\tests\units');

// Please replace in next line "/path/to/your/classes/directory" by your classes directory.
$testGenerator->setTestedClassesDirectory('path/to/your/classes/directory');

// Please replace in next line "your\project\namespace" by your project namespace.
$testGenerator->setTestedClassNamespace('your\project\namespace');

// Please replace in next line "path/to/your/tests/units/runner.php" by path to your unit test's runner.
$testGenerator->setRunnerPath('path/to/your/tests/units/runner.php');

$script->getRunner()->setTestGenerator($testGenerator);
*/

if (!is_dir(__DIR__.'/build/tests') && !mkdir(__DIR__.'/build/tests/', 0777, true) && !is_dir(__DIR__.'/build/tests')) {
	throw new \Exception("Unable to make directory ".__DIR__.'/build/tests/', 1);
}
if (!is_dir(__DIR__.'/build/coverage') && !mkdir(__DIR__.'/build/coverage/', 0777, true) && !is_dir(__DIR__.'/build/coverage')) {
	throw new \Exception("Unable to make directory ".__DIR__.'/build/coverage/', 1);
}

$xunit = new atoum\reports\asynchronous\xunit();
$runner->addReport($xunit);

$writer = new atoum\writers\file(__DIR__.'/build/tests/atoum.xunit.xml');
$xunit->addWriter($writer);

$clover = new atoum\reports\asynchronous\clover();
$runner->addReport($clover);

$writerClover = new atoum\writers\file(__DIR__.'/build/tests/atoum.clover.xml');
$clover->addWriter($writerClover);

$coverage = new coverage\html();
$coverage->addWriter(new std\out());
$coverage->setOutPutDirectory(__DIR__ . '/build/coverage');
$runner->addReport($coverage);


$script->addTestsFromDirectory(__DIR__.'/tests/Units');

