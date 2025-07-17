#!/bin/bash

echo "🚀 Building Laravel assets for deployment..."

# Install dependencies if needed
echo "📦 Installing dependencies..."
npm install

# Build the assets
echo "🔨 Building assets with Vite..."
npm run build

# Check if build was successful
if [ $? -eq 0 ]; then
    echo "✅ Build successful!"
    echo ""
    echo "📁 Built assets are in: public/build/"
    echo "📄 Manifest file: public/build/manifest.json"
    echo ""
    echo "🔍 Checking build output..."
    
    if [ -f "public/build/manifest.json" ]; then
        echo "✅ Manifest file exists"
        echo "📊 Manifest contents:"
        cat public/build/manifest.json | head -20
    else
        echo "❌ Manifest file not found!"
        exit 1
    fi
    
    echo ""
    echo "📋 Files to upload to server:"
    echo "   - public/build/ (entire directory)"
    echo ""
    echo "🌐 Server path should be:"
    echo "   /home/u546312304/domains/green-wolverine-495039.hostingersite.com/public_html/public/build/"
    echo ""
    echo "📤 Upload instructions:"
    echo "   1. Upload the entire 'public/build/' directory to your server"
    echo "   2. Make sure it goes to: public_html/public/build/"
    echo "   3. Verify manifest.json exists at: public_html/public/build/manifest.json"
    echo ""
    echo "🔧 After uploading, run on server:"
    echo "   php artisan config:clear"
    echo "   php artisan cache:clear"
    echo "   php artisan view:clear"
    echo ""
    echo "🎯 Alternative: If you can't upload, try this on the server:"
    echo "   npm install"
    echo "   npm run build"
    
else
    echo "❌ Build failed!"
    exit 1
fi 