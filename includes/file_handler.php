<?php
class FileHandler {
    private $allowedTypes = [
        'application/pdf' => ['icon' => 'fa-file-pdf', 'preview' => true],
        'image/jpeg' => ['icon' => 'fa-file-image', 'preview' => true],
        'image/png' => ['icon' => 'fa-file-image', 'preview' => true],
        'image/gif' => ['icon' => 'fa-file-image', 'preview' => true],
        'application/msword' => ['icon' => 'fa-file-word', 'preview' => false],
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => ['icon' => 'fa-file-word', 'preview' => false],
        'application/vnd.ms-excel' => ['icon' => 'fa-file-excel', 'preview' => false],
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => ['icon' => 'fa-file-excel', 'preview' => false]
    ];

    public function __construct() {
        $this->uploadDir = UPLOAD_DIR;
        
        // Create upload directory if it doesn't exist
        if (!file_exists($this->uploadDir)) {
            mkdir($this->uploadDir, 0777, true);
        }
    }
    
    public function validateFile($file) {
        if (!isset($file['type']) || !array_key_exists($file['type'], $this->allowedTypes)) {
            return "Invalid file type. Allowed types are: PDF, JPEG, PNG, DOC, DOCX, XLS, XLSX";
        }

        $maxSize = 5 * 1024 * 1024; // 5MB
        if ($file['size'] > $maxSize) {
            return "File is too large. Maximum size is 5MB.";
        }

        return true;
    }

    public function uploadFile($file) {
        $validation = $this->validateFile($file);
        if ($validation !== true) {
            return ['error' => $validation];
        }

        $fileName = uniqid() . '_' . basename($file['name']);
        $targetPath = $this->uploadDir . $fileName;

        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            return [
                'success' => true,
                'path' => $fileName,
                'type' => $file['type'],
                'preview' => $this->allowedTypes[$file['type']]['preview']
            ];
        }

        return ['error' => 'Failed to upload file.'];
    }

    public function getFileInfo($filePath) {
        $fullPath = $this->uploadDir . $filePath;
        if (!file_exists($fullPath)) {
            return false;
        }

        $mimeType = mime_content_type($fullPath);
        if (!array_key_exists($mimeType, $this->allowedTypes)) {
            return false;
        }

        return [
            'type' => $mimeType,
            'icon' => $this->allowedTypes[$mimeType]['icon'],
            'preview' => $this->allowedTypes[$mimeType]['preview'],
            'size' => filesize($fullPath),
            'name' => basename($filePath)
        ];
    }

    public function canPreview($filePath) {
        $info = $this->getFileInfo($filePath);
        return $info && $info['preview'];
    }

    public function getFileIcon($fileType) {
        return isset($this->allowedTypes[$fileType]) ? $this->allowedTypes[$fileType]['icon'] : 'fa-file';
    }

    public function formatFileSize($bytes) {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, 2) . ' ' . $units[$pow];
    }

    public function deleteFile($filename) {
        $filepath = $this->uploadDir . $filename;
        
        if (file_exists($filepath)) {
            return unlink($filepath);
        }
        
        return false;
    }
}
?>
