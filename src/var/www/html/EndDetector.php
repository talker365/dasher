<?php

namespace HPierce;

class EndDetector {
    public function wasEndRequested()
    {
        $wasEndRequested = file_exists('../end.txt');

        if($wasEndRequested) {
            unlink('../end.txt');
        }

        return $wasEndRequested;
    }

    public function requestEnd()
    {
        file_put_contents(__DIR__ . '/../end.txt', 'end was requested');
    }
}
?>