<?php

require '../vendor/autoload.php';

use Symfony\Component\Process\Process;
use HPierce\EndDetector;

?>
<style>
    * {
        font-family: "Courier New", Courier, monospace;
        color:white;
    }
</style>
<?php

$process = new Process('ping www.google.com');
$process->start();

$endDetector = new EndDetector();

$wasEndRequested = isset($_POST['status']) && $_POST['status'] == 'end';
if($wasEndRequested) {
    $endDetector->requestEnd();
    echo "End Requested";
} else {
    while(!$endDetector->wasEndRequested()) {
        sleep(1);

        $output = $process->getIncrementalOutput();

        if(!empty($output)) {
            echo $output . "<br>";
            $output = null;
            ob_flush();
        }
    }

    echo "Shell closed.<br>";
}
