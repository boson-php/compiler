<?php

Phar::mapPhar('{name}.phar');

if (\is_file(__DIR__ . '/libboson-darwin-universal.dylib')) {
    Phar::mount('libboson-darwin-universal.dylib', __DIR__ . '/libboson-darwin-universal.dylib');
}

if (\is_file(__DIR__ . '/libboson-linux-aarch64.so')) {
    Phar::mount('libboson-linux-aarch64.so', __DIR__ . '/libboson-linux-aarch64.so');
}

if (\is_file(__DIR__ . '/libboson-linux-x86_64.so')) {
    Phar::mount('libboson-linux-x86_64.so', __DIR__ . '/libboson-linux-x86_64.so');
}

if (\is_file(__DIR__ . '/libboson-windows-x86_64.dll')) {
    Phar::mount('libboson-windows-x86_64.dll', __DIR__ . '/libboson-windows-x86_64.dll');
}

foreach ({mount} as $mountEntrypoint) {
    if (\is_dir(__DIR__ . '/' . $mountEntrypoint) || \is_file(__DIR__ . '/' . $mountEntrypoint)) {
        Phar::mount($mountEntrypoint, __DIR__ . '/' . $mountEntrypoint);
    }
}

unset($mountEntrypoint);

Phar::interceptFileFuncs();

$_SERVER['SCRIPT_FILENAME'] = 'phar://{name}.phar/{entrypoint}';

require 'phar://{name}.phar/{entrypoint}';

__HALT_COMPILER();
