<?php

namespace Doc\Application;

use Zend_Controller_Action_HelperBroker;
use Doc\Domain\Library;
use Zend_File_Transfer_Adapter_Http;
use Doc\Domain\Document;
use Zend_Registry;
use Core_Exception_InvalidArgument;
use Core_Exception;
use Core_Exception_NotFound;

/**
 * Adaptateur ZF pour le transfert de document.
 * @author thibaud.rolland
 * @author matthieu.napoli
 */
class FileAdapter
{

    /**
     * @var array Messages d'erreur
     */
    private $message = array();

    /**
     * @var string
     */
    private $basePath;

    /**
     * @var \Doc\Domain\Library
     */
    private $library;
    /**
     * @var Document
     */
    private $document;

    /**
     * @var Zend_File_Transfer_Adapter_Http
     */
    private $transferAdapter;

    /**
     * options de parametres du validateur -- validator option parameters
     * @var array
     */
    private $validator = array();


    /**
     * @param \Doc\Domain\Library $library
     * @throws Core_Exception
     */
    public function __construct(Library $library)
    {
        $this->library = $library;
        $this->transferAdapter = new Zend_File_Transfer_Adapter_Http();
        $config = Zend_Registry::get('configuration');
        if (!$config->documents->path) {
            throw new Core_Exception('The configuration should contain the target document path');
        }
        $this->basePath = $config->documents->path;
        $this->transferAdapter->setDestination($this->basePath);
    }

    /**
     * Réception du document sur le serveur
     * @param  string|array $documents (Optional) Name of the file when multi files uploaded defined with tag name
     * @throws Core_Exception_InvalidArgument
     * @return bool
     */
    public function receive($documents = null)
    {
        if ($this->transferAdapter->hasErrors()) {
            $this->message = $this->transferAdapter->getMessages();
            return false;
        }
        if (!$this->transferAdapter->isUploaded()) {
            $this->message[] = __('Doc', 'library', 'noDocumentGiven');
            return false;
        }
        if (!$this->transferAdapter->isValid()) {
            $this->message[] = __('Doc', 'library', 'invalidMIMEType');
            return false;
        }
        if ($this->transferAdapter->getFileName() == null) {
            $this->message[] = __('Doc', 'messages', 'uploadError');
            return false;
        }
        // Teste si le document existe
        if (!$this->transferAdapter->receive($documents)) {
            $this->message = $this->transferAdapter->getMessages();
            return false;
        }

        $filePath = $this->basePath . DIRECTORY_SEPARATOR . $this->transferAdapter->getFileName(null, false);

        $this->document = new Document($this->library, $filePath);
        $this->document->save();

        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();

        // Renomme le fichier avec l'ID du document
        $dirName = pathinfo($this->document->getFilePath(), PATHINFO_DIRNAME);
        $extension = pathinfo($this->document->getFilePath(), PATHINFO_EXTENSION);
        $newFilePath = $dirName . DIRECTORY_SEPARATOR . $this->document->getId() . '.' . $extension;
        rename($this->document->getFilePath(), $newFilePath);
        $this->document->setFilePath($newFilePath);
        $this->document->save();

        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();

        return true;
    }

    /**
     * mise à jour du fichier pour remplacer la version précédente du serveur
     *  -- update the file from the client (Upload)
     * @param  Document $oldDocument document to update
     * @param  string|array       $documents   (Optional) Name of the file when multi files uploaded defined with tag name
     * @return bool
     */
    public function update($oldDocument, $documents = null)
    {
        if (!$this->transferAdapter->isUploaded()) {
            throw new Core_Exception_InvalidArgument(__('Doc', 'library', 'uploadError'));
        }
        $this->transferAdapter->setDestination($this->basePath);

        if (!$this->transferAdapter->receive($documents)) {
            $this->message = $this->transferAdapter->getMessages();
            throw new Core_Exception_InvalidArgument(__('Doc', 'library', 'downloadError'));
        }
        if ($oldDocument->getFileExists()) {
            if (!unlink($this->basePath . DIRECTORY_SEPARATOR . $oldDocument->getKey() . '.' . $oldDocument->getFileExtension())) {
                throw new Core_Exception_InvalidArgument(__('Doc', 'messages', 'deleteFileError'));
            }
        }
        $oldDocument->setDate(date("Y-m-d"));
        $filename = $this->transferAdapter->getFileName(null, false);
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        $oldDocument->setExtension($ext);
        $oldDocument->setSizeMo($this->transferAdapter->getFileSize());

        $oldDocument->setMimeType($this->mimeContentType($filename));
        $oldDocument->setType($this->setType($oldDocument->getMimeType()));
        $oldDocument->setFileExists(true);
        $oldDocument->setFileName(pathinfo($filename, PATHINFO_FILENAME));
        $oldDocument->save();
        $this->changeFileName($oldDocument, (string) $oldDocument->getKey());
        $oldDocument = Doc_Model_Command_Management::getInstance()->updateFile($oldDocument->getKey());
        return $oldDocument;
    }

    /**
     * Supprime le document
     * @param Document $document
     * @throws Core_Exception
     */
    public static function deleteDocumentFile(Document $document)
    {
        if (file_exists($document->getFilePath())) {
            if (unlink($document->getFilePath())) {
                return;
            } else {
                throw new Core_Exception("Unable to delete file " . $document->getFilePath());
            }
        }
    }

    /**
     * @return array
     */
    public function getMessages()
    {
        return $this->message;
    }

    /**
     * Modifie les restrictions de taille du document
     *
     * @param int|string $max
     * @param int|string $min
     */
    public function setMaxDocumentSize($max, $min = 0)
    {
        $this->validator['size'] = array('min' => $min, 'max' => $max);
    }

    /**
     * Modifie le 'MimeType' du validateur pour restreindre les fichiers à certains types
     *
     * @param string[] $fileTypes
     */
    public function allowDocumentTypes(array $fileTypes)
    {
        $mimeTypes = array();
        foreach ($fileTypes as $fileType) {
            $mimeTypes = array_merge($mimeTypes, $this->getMimeTypes($fileType));
        }
        $mimeTypes['headerCheck'] = true;
        $this->validator['MimeType'] = $mimeTypes;
    }

    /**
     * Modifie le 'MimeType' du validateur pour exclure certains types de fichiers
     *
     * @param string $fileType
     */
    public function excludeDocumentType($fileType)
    {
        if (is_string($fileType)) {
            $typeArray = $this->getMimeTypes($fileType);
            $typeArray['headerCheck'] = true;
            $this->validator['ExcludeMimeType'] = $typeArray;
        }
        if (is_array($fileType)) {
            $typeArray = array();
            foreach ($fileType as $type) {
                $typeArray = array_merge($typeArray, $this->getMimeTypes($type));
            }
            $typeArray['headerCheck'] = true;
            $this->validator['ExcludeMimeType'] = $typeArray;
        }
    }

    /**
     * Ajout des validateurs -- add the validators
     *
     * @param  string|array $validators (Optional) user defined validators
     * @param  string|array $files      (Optional) Files to check
     * @throws Core_Exception_InvalidArgument
     */
    public function addValidators($validators = null, $files = null)
    {
        if ($validators == null) {
            if (isset($this->validator)) {
                $this->transferAdapter->addValidators($this->validator, $files);
            } else {
                throw new Core_Exception_InvalidArgument(__('Doc', 'messages', 'addValidatorConfig'));
            }
        } else {
            $this->validator[$validators[0]] = $validators[1];
            $this->transferAdapter->addValidators($this->validator, $files);
        }
    }

    /**
     * vérifie que les fichiers sont valides -- Checks if the files are valid
     *
     * @param  string|array $files (Optional) Files to check
     * @return boolean True if all checks are valid
     */
    public function isValid($files = null)
    {
        if ($this->transferAdapter->isValid($files)) {
            return true;
        } else {
            $this->message = $this->transferAdapter->getMessages();
            return false;
        }
    }

    /**
     * méthode pour récupérer le document -- Method for downloading the document
     *
     * @param \Doc\Domain\Document $document
     * @throws Core_Exception_NotFound
     */
    public static function downloadDocument(Document $document)
    {
        $filePath = $document->getFilePath();

        if (!file_exists($filePath)) {
            throw new Core_Exception_NotFound("The file " . $document->getName() . " doesn't exist");
        }

        Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer')->setNoRender(true);
        Zend_Controller_Action_HelperBroker::getStaticHelper('layout')->disableLayout();

        $mimeType = self::mimeContentType(pathinfo($filePath, PATHINFO_BASENAME));

        $downloadBaseName = self::sanitizeFileName(
            $document->getName()
            . '.' . pathinfo($filePath, PATHINFO_EXTENSION)
        );

        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Component: must-revalidate, post-check=0, pre-check=0");
        header("Content-type:" . $mimeType);
        header("Content-Length: " . $document->getFileSize());
        if (preg_match("/.*MSIE .*/", $_SERVER ["HTTP_USER_ AGENT"])) {
            header('Content-Disposition: attachment; filename="' . rawurlencode($downloadBaseName) . '"');
        } else {
            header('Content-Disposition: attachment; filename="' . $downloadBaseName . '"');
        }
        header('Content-Transfer-Encoding: binary');

        readfile($filePath);
    }

    /**
     * Retourne les types Mime correspondant à un type de fichier
     *
     * @param string $type Type de fichier
     * @return array Types mimes
     */
    static public function getMimeTypes($type)
    {
        switch ($type) {
            case 'document':
                return array(
                    'application/pdf',
                    'application/x-pdf',
                    'application/vnd.ms-excel',
                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    'application/msword',
                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    'application/vnd.ms-powerpoint',
                    'application/vnd.oasis.opendocument.text',
                    'application/vnd.oasis.opendocument.spreadsheet',
                    'application/vnd.oasis.opendocument.presentation',
                    'application/vnd.oasis.opendocument.graphics',
                    'application/vnd.mozilla.xul+xml'
                );
            case 'audio':
                return array(
                    'audio/basic',
                    'audio/mid',
                    'audio/mpeg',
                    'audio/x-wav',
                    'audio/x-pn-realaudio',
                    'audio/x-ms-wma',
                    'audio/x-pn-realaudio-plugin',
                    'audio/x-aiff'
                );
            case 'video':
                return array(
                    'audio/mpeg',
                    'video/jpeg',
                    'video/x-ms-wmv',
                    'video/mp4',
                    'video/quicktime',
                    'video/x-flv',
                    'video/x-fli',
                    'video/x-msvideo',
                    'video/3gpp',
                    'video/3gpp2',
                    'video/x-ms-wm',
                    'application/vnd.rn-realmedia',
                    'application/x-shockwave-flash',
                    'video/vnd.rn-realvideo'
                );
            case 'application':
                return array(
                    'application/octet-stream',
                    'application/x-gzip',
                    'application/x-gtar',
                    'application/x-compress',
                    'application/x-compressed',
                    'application/x-javascript',
                    'application/x-iphone',
                    'application/x-msdownload',
                    'application/x-sh',
                    'application/zip',
                    'application/x-jam',
                    'application/java-archive',
                    'application/x-java-jnlp-file',
                    'applicaiton/x-java',
                    'application/x-rar-compressed',
                    'application/x-rar',
                    'application/x-7z-compressed'
                );
            case 'image':
                return array(
                    'image/bmp',
                    'image/gif',
                    'image/x-png',
                    'image/png',
                    'image/jpeg',
                    'image/pjpeg',
                    'image/tiff',
                    'image/vnd.microsoft.icon',
                    'image/svg+xml'
                );
            case 'text':
                return array(
                    'text/css',
                    'text/html',
                    'text/plain',
                    'text/richtext',
                    'text/java',
                    'text/csv',
                    'text/rtf',
                    'application/xhtml+xml',
                    'application/json',
                    'text/javascript',
                    'text/xml',
                    'text/template'
                );
            case 'pdf':
                return array(
                    'application/pdf',
                    'application/x-pdf',
                );
            case 'excel':
                return array(
                    'application/vnd.ms-excel',
                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                );
            case 'word' :
                return array(
                    'application/msword',
                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
                );
            case 'powerpoint':
                return array(
                    'application/vnd.ms-powerpoint'
                );
            case 'jpg':
                return array(
                    'image/jpeg',
                    'image/pjpeg'
                );
            case 'png':
                return array(
                    'image/x-png',
                    'image/png'
                );
            case 'gif':
                return array(
                    'image/gif',
                );
            case 'bmp':
                return array(
                    'image/bmp',
                );
        }
        return array();
    }

    /**
     * Set the mime-type of the document.
     * @param string $mimeType
     * @return null|string DocumentType
     */
    static public function setType($mimeType)
    {
        switch ($mimeType) {
            case 'application/pdf':
            case 'application/x-pdf':
                return 'PDF';
            case 'application/vnd.ms-excel':
            case 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet':
            case 'application/vnd.oasis.opendocument.spreadsheet':
                return 'tableur';
            case 'application/msword':
            case 'application/vnd.openxmlformats-officedocument.wordprocessingml.document':
                return 'traitement de texte';
            case 'application/vnd.ms-powerpoint':
            case 'application/vnd.oasis.opendocument.presentation':
                return 'présentation';
            case 'text/css':
            case 'text/html':
            case 'text/plain':
            case 'text/richtext':
            case 'text/java':
            case 'text/csv':
            case 'text/rtf':
            case 'application/xhtml+xml':
            case 'application/json':
            case 'text/javascript':
            case 'text/xml':
            case 'text/template':
            case 'application/vnd.oasis.opendocument.text':
                return 'texte';
            case 'image/bmp':
            case 'image/gif':
            case 'image/x-png':
            case 'image/png':
            case 'image/jpeg':
            case 'image/pjpeg':
            case 'image/tiff':
            case 'image/vnd.microsoft.icon':
            case 'image/svg+xml':
                return 'image';
            case 'application/vnd.oasis.opendocument.graphics':
                return 'OPENOFFICE';
            case 'application/octet-stream':
                return 'APPLICATION';
            case 'application/x-rar-compressed':
            case 'application/x-rar':
            case 'application/x-7z-compressed':
            case 'application/zip':
                return 'Dossier zippé';
            default:
                return null;
        }
    }

    /**
     * @param string $filename
     * @return string $mimetype
     */
    static public function mimeContentType($filename)
    {
        $mimeTypes = array(
            'txt'  => 'text/plain',
            // images
            'png'  => 'image/png',
            'jpe'  => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'jpg'  => 'image/jpeg',
            'gif'  => 'image/gif',
            'bmp'  => 'image/bmp',
            'ico'  => 'image/vnd.microsoft.icon',
            'tiff' => 'image/tiff',
            'tif'  => 'image/tiff',
            'svg'  => 'image/svg+xml',
            'svgz' => 'image/svg+xml',
            // archives
            '7z'   => 'application/x-7z-compressed',
            'zip'  => 'application/zip',
            'rar'  => 'application/x-rar-compressed',
            'cab'  => 'application/vnd.ms-cab-compressed',
            // adobe
            'pdf'  => 'application/pdf',
            'psd'  => 'image/vnd.adobe.photoshop',
            'ai'   => 'application/postscript',
            'eps'  => 'application/postscript',
            'ps'   => 'application/postscript',
            // ms office
            'pub'  => 'application/x-mspublisher',
            'docx' => 'application/msword',
            'xlsx' => 'application/vnd.ms-excel',
            'pptx' => 'application/vnd.ms-powerpoint',
            'doc'  => 'application/msword',
            'rtf'  => 'application/rtf',
            'xls'  => 'application/vnd.ms-excel',
            'ppt'  => 'application/vnd.ms-powerpoint',
            'pptm' => 'application/vnd.ms-powerpoint',
            // open office
            'odt'  => 'application/vnd.oasis.opendocument.text',
            'ods'  => 'application/vnd.oasis.opendocument.spreadsheet',
        );

        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        $ext = strtolower($ext);
        if (array_key_exists($ext, $mimeTypes)) {
            return $mimeTypes[$ext];
        } elseif (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME);
            $mimetype = finfo_file($finfo, $filename);
            finfo_close($finfo);
            return $mimetype;
        } else {
            return 'application/octet-stream';
        }
    }

    /**
     * @param string $fileName
     * @return string
     */
    private static function sanitizeFileName($fileName)
    {
        $specialChars = array(
            "?",
            "[",
            "]",
            "/",
            "\\",
            "=",
            "<",
            ">",
            ":",
            ";",
            ",",
            "'",
            "\"",
            "&",
            "$",
            "#",
            "*",
            "(",
            ")",
            "|",
            "~",
            "`",
            "!",
            "{",
            "}"
        );
        $fileName = str_replace($specialChars, '', $fileName);
        $fileName = preg_replace('/[\s-]+/', '-', $fileName);
        $fileName = trim($fileName, '.-_');
        return $fileName;
    }

}
