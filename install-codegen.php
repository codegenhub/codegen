<?php

// Define the name of the package
$packageName = 'codegenhub/codegen';

// Determine the OS
$os = strtoupper(substr(PHP_OS, 0, 3));

// Check if global composer is available
$composerCommand = 'composer';
if (trim(shell_exec('which composer')) == '') {
    echo "Global Composer not found. Downloading composer.phar...\n";
    file_put_contents('composer.phar', fopen('https://getcomposer.org/composer-stable.phar', 'r'));
    $composerCommand = 'php composer.phar';
}

// Install the package globally using Composer
echo "Installing $packageName via Composer...\n";
shell_exec($composerCommand . ' global require ' . $packageName);

// Determine the path for the global Composer bin directory
$composerBinDir = rtrim(shell_exec($composerCommand . ' global config bin-dir --absolute'), "\n");

// Symlink or copy the codegen binary to a directory in the user's PATH
$binaryPath = $composerBinDir . '/codegen';
$destPath = $os === 'WIN' ? 'C:/Windows/System32/codegen' : '/usr/local/bin/codegen';

if (file_exists($destPath)) {
    echo "Removing existing codegen command...\n";
    unlink($destPath);
}

if ($os !== 'WIN') {
    echo "Creating symlink to codegen...\n";
    if (!symlink($binaryPath, $destPath)) {
        echo "Failed to create symlink. Please ensure you have the necessary permissions.\n";
        exit(1);
    }
    chmod($destPath, 0755);
} else {
    echo "Copying codegen binary...\n";
    if (!copy($binaryPath, $destPath)) {
        echo "Failed to copy binary. Please ensure you have the necessary permissions.\n";
        exit(1);
    }
}

// Remove local composer.phar if it was downloaded
if ($composerCommand === 'php composer.phar') {
    unlink('composer.phar');
}

echo "Installation successful! You can now use the 'codegen' command.\n";
