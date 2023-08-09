<?php

// Define the name of the package
$packageName = 'codegenhub/codegen';

// Determine the OS
$os = strtoupper(substr(PHP_OS, 0, 3));

// Check if global composer is available
$composerCommand = 'composer';
$whichComposer = shell_exec('which composer');
if (!$whichComposer || trim($whichComposer) == '') {
    echo "Global Composer not found. Downloading composer.phar...\n";
    file_put_contents('composer.phar', fopen('https://getcomposer.org/composer-stable.phar', 'r'));
    $composerCommand = 'php composer.phar';
    echo "\e[32m✓\e[0m Downloaded composer.phar\n";
}

// Install the package globally using Composer with --no-plugins flag
echo "Installing $packageName via Composer...\n";
shell_exec($composerCommand . ' global require ' . $packageName . ' --no-plugins');
echo "\e[32m✓\e[0m Installed $packageName\n";

// Determine the path for the global Composer bin directory
$composerBinDirOutput = shell_exec($composerCommand . ' global config bin-dir --absolute');
$composerBinDir = $composerBinDirOutput ? rtrim($composerBinDirOutput, "\n") : '';

if (!$composerBinDir) {
    echo "Failed to determine Composer bin directory. Exiting.\n";
    exit(1);
}

// Check if the codegen executable exists at the expected path
$binaryPath = $composerBinDir . '/codegen';
if (!file_exists($binaryPath)) {
    // Adjust the path based on the relative vendor directory structure
    $binaryPath = $composerBinDir . '/vendor/codegenhub/codegen/codegen';
    if (!file_exists($binaryPath)) {
        echo "The codegen executable was not found at the expected paths.\n";
        exit(1);
    }
}

// Symlink or copy the codegen binary to a directory in the user's PATH
$destPath = $os === 'WIN' ? 'C:/Windows/System32/codegen' : getenv('HOME') . '/.local/bin/codegen';

// Ensure the target directory exists (for UNIX systems)
if ($os !== 'WIN' && !is_dir(dirname($destPath))) {
    mkdir(dirname($destPath), 0755, true);
}

if (file_exists($destPath)) {
    echo "Removing existing codegen command...\n";
    unlink($destPath);
}

if ($os !== 'WIN') {
    echo "Creating symlink to codegen...\n";
    if (!symlink($binaryPath, $destPath)) {
        echo "Failed to create symlink. Please ensure you have the necessary permissions or choose a different installation directory.\n";
        exit(1);
    }
    chmod($destPath, 0755);
    echo "\e[32m✓\e[0m Created symlink to codegen\n";
} else {
    echo "Copying codegen binary...\n";
    if (!copy($binaryPath, $destPath)) {
        echo "Failed to copy binary. Please ensure you have the necessary permissions.\n";
        exit(1);
    }
    echo "\e[32m✓\e[0m Copied codegen binary\n";
}

// Remove local composer.phar if it was downloaded
if ($composerCommand === 'php composer.phar') {
    unlink('composer.phar');
    echo "\e[32m✓\e[0m Removed local composer.phar\n";
}

echo "\e[32m✓\e[0m Installation successful! You can now use the 'codegen' command.\n";
echo "Ensure that ~/.local/bin is in your PATH.\n";
