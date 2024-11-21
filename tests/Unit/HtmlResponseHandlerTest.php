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

test('throws exception when template file does not exist', function () {
    $handler = new HtmlResponseHandler();
    $nonExistentFile = '/path/to/nonexistent/file.html';
    
    expect(fn() => $handler->handle($nonExistentFile))
        ->toThrow(RuntimeException::class, 'Failed to read template file');
});

test('handles empty template file correctly', function () {
    $handler = new HtmlResponseHandler();
    $tempFile = tempnam(sys_get_temp_dir(), 'test');
    file_put_contents($tempFile, '');
    
    ob_start();
    try {
        $handler->handle($tempFile);
    } catch (Exception $e) {
        // Catch the exit() call
    }
    $output = ob_get_clean();
    unlink($tempFile);
    
    expect($output)->toBe('');
});

test('handles special characters in template content', function () {
    $handler = new HtmlResponseHandler();
    $tempFile = tempnam(sys_get_temp_dir(), 'test');
    $content = '<html>Special chars: &copy; &reg; &trade; &euro; &lt; &gt;</html>';
    file_put_contents($tempFile, $content);
    
    ob_start();
    try {
        $handler->handle($tempFile);
    } catch (Exception $e) {
        // Catch the exit() call
    }
    $output = ob_get_clean();
    unlink($tempFile);
    
    expect($output)->toBe($content);
});
