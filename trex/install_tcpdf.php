<?php
// Optional: If you want to install TCPDF for proper PDF generation
// Run this file once to download and set up TCPDF

$tcpdf_url = 'https://github.com/tecnickcom/TCPDF/archive/main.zip';
$tcpdf_dir = 'tcpdf';

if (!file_exists($tcpdf_dir)) {
    echo "Downloading TCPDF...\n";
    
    // Download TCPDF
    $zip_content = file_get_contents($tcpdf_url);
    if ($zip_content === false) {
        die("Failed to download TCPDF\n");
    }
    
    // Save zip file
    file_put_contents('tcpdf.zip', $zip_content);
    
    // Extract zip
    $zip = new ZipArchive;
    if ($zip->open('tcpdf.zip') === TRUE) {
        $zip->extractTo('./');
        $zip->close();
        
        // Rename extracted folder
        rename('TCPDF-main', $tcpdf_dir);
        
        // Clean up
        unlink('tcpdf.zip');
        
        echo "TCPDF installed successfully!\n";
        echo "You can now use proper PDF generation.\n";
    } else {
        die("Failed to extract TCPDF\n");
    }
} else {
    echo "TCPDF already installed.\n";
}
?>
