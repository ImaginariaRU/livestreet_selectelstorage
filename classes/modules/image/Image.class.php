<?php
/*-------------------------------------------------------
*
*	Plugin: Selectel Cloud Storage
*	Author: Dmitry Lakhno (kexek)
*	Website: http://kexek.me
*	E-mail: mail@kexek.me
*
*--------------------------------------------------------
*/

class PluginSelectelstorage_ModuleImage extends PluginSelectelstorage_Inherit_ModuleImage {

    /*
     * Сохранение в Selectel Storage
     */
    public function UploadToSelectelstorage($sFilePath) {
        if (extension_loaded('curl')) {
            $sS = new SelectelStorage(Config::Get('plugin.selectelstorage.user'), Config::Get('plugin.selectelstorage.password'));

            try {
                $bucket = $sS->getContainer(Config::Get('plugin.selectelstorage.bucket'));

                $sName = explode(trim(Config::Get('path.uploads.root')).'/', $sFilePath);
                $sName = array_pop($sName);
                $sName = strtolower($sName);

                if ($bucket->putFile($sFilePath, $sName)) {
                    @unlink($sFilePath);
                    return 'http://' . Config::Get('plugin.selectelstorage.domain') . '/'. $sName;
                }
                else {
                    error_log('Something wrong while uploading file to selectel storage');
                    return $sFilePath;
                }
            }
            catch(Exception $e) {
                error_log("Something wrong while uploading file to selectel storage. Error: ".$e->getMessage());
                return $sFilePath;
            }
        }
        return false;
    }

    /*
     * Изменяем логику сохранения файла
     */
    public function SaveFile($sFileSource,$sDirDest,$sFileDest,$iMode=null,$bRemoveSource=false) {
        $sFileDestFullPath = parent::SaveFile($sFileSource,$sDirDest,$sFileDest,$iMode,$bRemoveSource);
        if ($sFileDestFullPath && !strpos($sDirDest,'/tmp/avatars/') && !strpos($sDirDest,'/tmp/fotos/')) {
            $sFileDestFullPath = $this->UploadToSelectelStorage($sFileDestFullPath);
        }
        return $sFileDestFullPath;
    }

}

?>