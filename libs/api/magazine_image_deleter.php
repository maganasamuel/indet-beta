<?php
$target_dir = '../../../indet_photos_stash/';

if (! isset($_POST['filename'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Please provide a file name.',
    ]);

    return;
}

$filename = $_POST['filename'];

if (! $filename) {
    echo json_encode([
        'success' => false,
        'message' => 'Please provide a file name.',
    ]);

    return;
}

$path = $target_dir . $filename;

if (! file_exists($path)) {
    echo json_encode([
        'success' => false,
        'message' => 'File does not exist.',
    ]);

    return;
}

$deleted = unlink($path);

if (! $deleted) {
    echo json_encode([
        'success' => false,
        'message' => 'File was not deleted.',
    ]);

    return;
}

echo json_encode([
    'success' => true,
    'message' => 'File has been deleted.',
]);
