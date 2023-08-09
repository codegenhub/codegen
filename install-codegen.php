<?php

// Define the name of the package
$packageName = 'codegenhub/codegen';

// Determine the OS
$os = strtoupper(substr(PHP_OS, 0, 3));

// Check if global composer is available
$composerCommand = 'composer';
$whichComposer = shell_exec('which composer 2>&1');
$globalComposer = true;
if (!$whichComposer || trim($whichComposer) == '') {
    echo "Global Composer not found. Downloading composer.phar...\n";
    if (!@copy('https://getcomposer.org/composer-stable.phar', 'composer.phar')) {
        echo "Failed to download composer.phar. Please ensure you have an active internet connection.\n";
        exit(1);
    }
    $composerCommand = 'php composer.phar';
    $globalComposer = false;
    echo "\e[32m✓\e[0m Downloaded composer.phar\n";
}

// Install the package globally using Composer with --no-plugins flag
echo "Installing $packageName via Composer...\n";
if (!$globalComposer) {
    chdir(getenv('HOME') . '/.composer'); // Change to global composer directory
}
shell_exec($composerCommand . ' global require ' . $packageName . ' --no-plugins 2>&1');
echo "\e[32m✓\e[0m Installed $packageName\n";

// Determine the path for the global Composer packages
$composerGlobalVendorDirOutput = shell_exec($composerCommand . ' global config vendor-dir --absolute 2>&1');
$composerGlobalVendorDir = $composerGlobalVendorDirOutput ? rtrim($composerGlobalVendorDirOutput, "\\n") : '';

if (!$composerGlobalVendorDir) {
    echo "Failed to determine Composer global vendor directory. Exiting.\n";
    exit(1);
}

// Check if the codegen executable exists at the expected path
$binaryPath = $composerGlobalVendorDir . '/codegenhub/codegen/manage.php';

if (!file_exists($binaryPath)) {
    echo "The codegen executable was not found at the expected paths.\n";
    exit(1);
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
if (!$globalComposer) {
    unlink('composer.phar');
    echo "\e[32m✓\e[0m Removed local composer.phar\n";
}

echo "\e[32m✓\e[0m Installation successful! You can now use the 'codegen' command.\n";

// Add ~/.local/bin to PATH for UNIX systems
if ($os !== 'WIN') {
    $bashrcPath = getenv('HOME') . '/.bashrc';
    $zshrcPath = getenv('HOME') . '/.zshrc';
    $pathExport = 'export PATH=\"$HOME/.local/bin:$PATH\"';
    $updatedPath = false;
    if (file_exists($bashrcPath) && !strpos(file_get_contents($bashrcPath), $pathExport)) {
        file_put_contents($bashrcPath, $pathExport . "\\n", FILE_APPEND);
        echo "Added ~/.local/bin to PATH in .bashrc\n";
        $updatedPath = true;
    }
    if (file_exists($zshrcPath) && !strpos(file_get_contents($zshrcPath), $pathExport)) {
        file_put_contents($zshrcPath, $pathExport . "\\n", FILE_APPEND);
        echo "Added ~/.local/bin to PATH in .zshrc\n";
        $updatedPath = true;
    }
    if ($updatedPath) {
        echo "Ensure that ~/.local/bin is in your PATH or restart your terminal.\n";
    }
}
