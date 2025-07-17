<?php

echo "🔍 Server Diagnostic for Vite Assets\n";
echo "=====================================\n\n";

// Check Laravel version
echo "📋 Laravel Version: " . app()->version() . "\n";

// Check if manifest file exists
$manifestPath = public_path('build/manifest.json');
echo "📄 Manifest path: " . $manifestPath . "\n";
echo "📄 Manifest exists: " . (file_exists($manifestPath) ? "✅ YES" : "❌ NO") . "\n";

if (file_exists($manifestPath)) {
    echo "📊 Manifest size: " . filesize($manifestPath) . " bytes\n";
    echo "📊 Manifest readable: " . (is_readable($manifestPath) ? "✅ YES" : "❌ NO") . "\n";
    
    $manifest = json_decode(file_get_contents($manifestPath), true);
    if ($manifest) {
        echo "📊 Manifest valid JSON: ✅ YES\n";
        echo "📊 Number of entries: " . count($manifest) . "\n";
        
        // Show first few entries
        $count = 0;
        foreach ($manifest as $key => $value) {
            if ($count < 3) {
                echo "   - $key: " . (is_array($value) ? $value['file'] : $value) . "\n";
                $count++;
            }
        }
    } else {
        echo "📊 Manifest valid JSON: ❌ NO\n";
    }
}

// Check build directory
$buildDir = public_path('build');
echo "\n📁 Build directory: " . $buildDir . "\n";
echo "📁 Build directory exists: " . (is_dir($buildDir) ? "✅ YES" : "❌ NO") . "\n";

if (is_dir($buildDir)) {
    echo "📁 Build directory readable: " . (is_readable($buildDir) ? "✅ YES" : "❌ NO") . "\n";
    
    $files = scandir($buildDir);
    echo "📁 Files in build directory: " . count($files) . "\n";
    
    foreach ($files as $file) {
        if ($file !== '.' && $file !== '..') {
            $filePath = $buildDir . '/' . $file;
            $size = is_file($filePath) ? filesize($filePath) : 'DIR';
            echo "   - $file ($size)\n";
        }
    }
}

// Check Vite configuration
echo "\n⚙️ Vite Configuration:\n";
$viteConfigPath = base_path('vite.config.js');
echo "📄 Vite config exists: " . (file_exists($viteConfigPath) ? "✅ YES" : "❌ NO") . "\n";

// Check package.json
$packagePath = base_path('package.json');
echo "\n📦 Package.json exists: " . (file_exists($packagePath) ? "✅ YES" : "❌ NO") . "\n";

if (file_exists($packagePath)) {
    $package = json_decode(file_get_contents($packagePath), true);
    if (isset($package['scripts']['build'])) {
        echo "📦 Build script: " . $package['scripts']['build'] . "\n";
    }
}

// Check if node_modules exists
$nodeModulesPath = base_path('node_modules');
echo "📦 Node modules exists: " . (is_dir($nodeModulesPath) ? "✅ YES" : "❌ NO") . "\n";

// Check permissions
echo "\n🔐 Permissions:\n";
echo "📁 Public directory writable: " . (is_writable(public_path()) ? "✅ YES" : "❌ NO") . "\n";
echo "📁 Storage directory writable: " . (is_writable(storage_path()) ? "✅ YES" : "❌ NO") . "\n";

// Check environment
echo "\n🌍 Environment:\n";
echo "🔧 APP_ENV: " . config('app.env') . "\n";
echo "🔧 APP_DEBUG: " . (config('app.debug') ? "TRUE" : "FALSE") . "\n";

echo "\n🎯 Recommendations:\n";
if (!file_exists($manifestPath)) {
    echo "1. ❌ Manifest file missing - need to build assets\n";
    echo "2. 💡 Run: npm install && npm run build\n";
    echo "3. 💡 Or upload the public/build/ directory from your local machine\n";
} else {
    echo "1. ✅ Manifest file exists\n";
    echo "2. 💡 Clear Laravel caches: php artisan config:clear && php artisan cache:clear\n";
}

echo "\n🔧 Quick fixes to try:\n";
echo "1. php artisan config:clear\n";
echo "2. php artisan cache:clear\n";
echo "3. php artisan view:clear\n";
echo "4. php artisan route:clear\n";
echo "5. npm install && npm run build (if you have Node.js on server)\n"; 