<?php

class FileUpload {
    private $upload_dir;
    private $allowed_types;
    private $max_size;
    private $base_url;
    
    public function __construct($upload_dir = null, $base_url = 'http://localhost/projek_kelompok/uploads/') {
        $this->upload_dir = $upload_dir ?: __DIR__ . '/../../../uploads/';
        $this->allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        $this->max_size = 5 * 1024 * 1024; // 5MB
        $this->base_url = $base_url;
        
        // Pastikan folder uploads ada
        $this->createUploadDir();
    }
    
    /**
     * Buat folder upload jika belum ada
     */
    private function createUploadDir() {
        if (!is_dir($this->upload_dir)) {
            mkdir($this->upload_dir, 0755, true);
        }
    }
    
    /**
     * Upload file selfie
     */
    public function uploadSelfie($file, $id_karyawan) {
        try {
            // Validasi file
            $this->validateFile($file);
            
            // Generate nama file
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = 'selfie_' . $id_karyawan . '_' . date('YmdHis') . '.' . $ext;
            $file_path = $this->upload_dir . $filename;
            
            // Upload file
            if (!file_exists($file['tmp_name'])) {
                throw new Exception("File sementara tidak ditemukan: " . $file['tmp_name']);
            }
            
            if (move_uploaded_file($file['tmp_name'], $file_path)) {
                $file_url = $this->base_url . $filename;
                error_log("File uploaded successfully: " . $file_url);
                return $file_url;
            } else {
                throw new Exception("Gagal mengupload foto. Periksa permission folder uploads");
            }
            
        } catch (Exception $e) {
            error_log("Upload error: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Validasi file upload
     */
    private function validateFile($file) {
        // Cek error upload
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $upload_errors = [
                UPLOAD_ERR_INI_SIZE => 'File terlalu besar (php.ini)',
                UPLOAD_ERR_FORM_SIZE => 'File terlalu besar (form)',
                UPLOAD_ERR_PARTIAL => 'Upload tidak lengkap',
                UPLOAD_ERR_NO_FILE => 'Tidak ada file yang diupload',
                UPLOAD_ERR_NO_TMP_DIR => 'Folder temporary tidak ada',
                UPLOAD_ERR_CANT_WRITE => 'Gagal menulis file',
                UPLOAD_ERR_EXTENSION => 'Upload dihentikan oleh ekstensi'
            ];
            
            $error_message = $upload_errors[$file['error']] ?? 'Error upload tidak diketahui';
            throw new Exception($error_message);
        }
        
        // Validasi tipe file
        if (!in_array($file['type'], $this->allowed_types)) {
            throw new Exception("Tipe file tidak diizinkan. Hanya JPG, PNG, dan GIF yang diperbolehkan.");
        }
        
        // Validasi ukuran file
        if ($file['size'] > $this->max_size) {
            throw new Exception("Ukuran file terlalu besar. Maksimal " . ($this->max_size / 1024 / 1024) . "MB");
        }
    }
    
    /**
     * Hapus file
     */
    public function deleteFile($filename) {
        $file_path = $this->upload_dir . basename($filename);
        if (file_exists($file_path)) {
            return unlink($file_path);
        }
        return false;
    }
    
    /**
     * Set allowed file types
     */
    public function setAllowedTypes($types) {
        $this->allowed_types = $types;
    }
    
    /**
     * Set max file size
     */
    public function setMaxSize($size) {
        $this->max_size = $size;
    }
}