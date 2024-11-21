<?php

use App\HtmlResponseHandler;

test('handles html response correctly', function () {
    $handler = new HtmlResponseHandler();
    $tempFile = tempnam(sys_get_temp_dir(), 'test');
    file_put_contents($tempFile, '<html>Test Content</html>');
    
    ob_start();
    try {
        $handler->handle($tempFile);
    } catch (Exception $e) {
        // Catch the exit() call
    }
    $output = ob_get_clean();
    unlink($tempFile);
    
    expect($output)->toBe('<html>Test Content</html>');
});
