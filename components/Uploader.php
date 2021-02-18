<?php

namespace app\components;

use Yii;

class Uploader
{
    private $fileField;            
    private $file;                 
    private $config;               
    private $oriName;              
    private $fileName;             
    private $fullName;             
    private $fileSize;             
    private $fileType;             
    private $stateInfo;            
    private $stateMap = array(    
        "Thành công" ,               
        "Kích thước file vượt quá giới hạn " ,
        "Kích thước file vượt quá giới hạn " ,
        "File chưa được tải lên" ,
        "Không có file nào được tải lên" ,
        "File rỗng" ,
        "POST" => "Kích thước file vượt quá giới hạn " ,
        "SIZE" => "Kích thước file vượt quá giới hạn trang web" ,
        "TYPE" => "Định dạng file không được hỗ trợ" ,
        "DIR" => "Tạo thư mục không thành công" ,
        "IO" => "Lỗi input/output" ,
        "UNKNOWN" => "Lỗi không xác định" ,
        "MOVE" => "Lỗi khi lưu file",
        "DIR_ERROR" => "Không tạo được thư mục"
    );
    
    public function __construct( $fileField , $config = [], $base64 = false )
    {
        $this->fileField = $fileField;
        if (empty($config)) {
            $config = [
                'savePath' => 'uploads/' ,      
                'maxSize' => 2048 ,
                'allowFiles' => ['.gif' , '.png' , '.jpg' , '.jpeg' , '.bmp'],
            ];
        }
        $this->config = $config;
        $this->stateInfo = $this->stateMap[ 0 ];
        $this->upFile( $base64 );
    }
    
    private function upFile( $base64 )
    {
        if ( "base64" == $base64 ) {
            $content = $_POST[ $this->fileField ];
            $this->base64ToImage( $content );
            return;
        }
        $file = $this->file = $_FILES[ $this->fileField ];
        if ( !$file ) {
            $this->stateInfo = $this->getStateInfo( 'POST' );
            return;
        }
        if ( $this->file[ 'error' ] ) {
            $this->stateInfo = $this->getStateInfo( $file[ 'error' ] );
            return;
        }
        if ( !is_uploaded_file( $file[ 'tmp_name' ] ) ) {
            $this->stateInfo = $this->getStateInfo( "UNKNOWN" );
            return;
        }
        $this->oriName = $file[ 'name' ];
        $this->fileSize = $file[ 'size' ];
        $this->fileType = $this->getFileExt();
        if ( !$this->checkSize() ) {
            $this->stateInfo = $this->getStateInfo( "SIZE" );
            return;
        }
        if ( !$this->checkType() ) {
            $this->stateInfo = $this->getStateInfo( "TYPE" );
            return;
        }
        $folder = $this->getFolder();
        if ( $folder === false ) {
            $this->stateInfo = $this->getStateInfo( "DIR_ERROR" );
            return;
        }
        $this->fullName = $folder . '/' . $this->getName();
        if ( $this->stateInfo == $this->stateMap[ 0 ] ) {
            if ( !move_uploaded_file( $file[ "tmp_name" ] , $this->fullName ) ) {
                $this->stateInfo = $this->getStateInfo( "MOVE" );
            }
        }
    }

    private function base64ToImage( $base64Data )
    {
        $img = base64_decode( $base64Data );
        $this->fileName = time() . rand( 1 , 10000 ) . ".png";
        $this->fullName = $this->getFolder() . '/' . $this->fileName;
        if ( !file_put_contents( $this->fullName , $img ) ) {
            $this->stateInfo = $this->getStateInfo( "IO" );
            return;
        }
        $this->oriName = "";
        $this->fileSize = strlen( $img );
        $this->fileType = ".png";
    }

    public function getFileInfo()
    {
        return array(
            "originalName" => $this->oriName ,
            "name" => $this->fileName ,
            "url" => $this->fullName ,
            "size" => $this->fileSize ,
            "type" => $this->fileType ,
            "state" => $this->stateInfo
        );
    }

    private function getStateInfo( $errCode )
    {
        return !$this->stateMap[ $errCode ] ? $this->stateMap[ "UNKNOWN" ] : $this->stateMap[ $errCode ];
    }

    private function getName()
    {
        return $this->fileName = time() . rand( 1 , 10000 ) . $this->getFileExt();
    }

    private function checkType()
    {
        return in_array( $this->getFileExt() , $this->config[ "allowFiles" ] );
    }

    private function  checkSize()
    {
        return $this->fileSize <= ( $this->config[ "maxSize" ] * 1024 );
    }

    private function getFileExt()
    {
        return strtolower( strrchr( $this->file[ "name" ] , '.' ) );
    }
    private function getFolder()
    {
        $pathStr = $this->config[ "savePath" ];
        if ( strrchr( $pathStr , "/" ) != "/" ) {
            $pathStr .= "/";
        }
        $pathStr .= date( "Ymd" );
        if ( !file_exists( $pathStr ) ) {
            if ( !mkdir( $pathStr , 0777 , true ) ) {
                return false;
            }
        }
        return $pathStr;
    }
}
