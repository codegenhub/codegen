<?php

// Define the name of the package
$packageName = 'codegenhub/codegen';

// Determine the OS
$os = strtoupper(substr(PHP_OS, 0, 3));

// Install the package globally using Composer
echo "Installing $packageName via Composer...\n";
shell_exec('composer global require ' . $packageName);

// Determine the path for the global Composer bin directory
$composerBinDir = $os === 'WIN'
    ? shell_exec('composer global config bin-dir --absolute')
    : rtrim(shell_exec('composer global config bin-dir --absolute'), "\n");

// Symlink or copy the codegen binary to a directory in the user's PATH
$binaryPath = $composerBinDir . '/codegen';
$destPath = $os === 'WIN' ? 'C:/Windows/System32/codegen' : '/usr/local/bin/codegen';

if ($os !== 'WIN') {
    echo "Creating symlink to codegen...\n";
    symlink($binaryPath, $destPath);
} else {
    echo "Copying codegen binary...\n";
    copy($binaryPath, $destPath);
}

echo "Installation successful! You can now use the 'codegen' command.\n";
