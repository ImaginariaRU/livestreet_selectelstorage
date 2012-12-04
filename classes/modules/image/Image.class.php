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

			if ($bucket = $sS->getContainer(Config::Get('plugin.selectelstorage.bucket'))) {
	
				$exploded_filepath = explode(trim(Config::Get('path.uploads.root')).'/', $sFilePath);

				$sName = strtolower(array_pop($exploded_filepath));

				if ($bucket->putFile($sFilePath, $sName)) {
					@unlink($sFilePath);
					return 'http://' . Config::Get('plugin.selectelstorage.domain') . '/'. $sName;
				}
			}
		}

		return false;
	}

	/*
	 * Изменяем логику сохранения файла
	 */

	public function SaveFile($sFileSource,$sDirDest,$sFileDest,$iMode=null,$bRemoveSource=false) {
		$sFileDestFullPath=rtrim(Config::Get('path.root.server'),"/").'/'.trim($sDirDest,"/").'/'.$sFileDest;
		$this->CreateDirectory($sDirDest);

		$bResult=copy($sFileSource,$sFileDestFullPath);
		if ($bResult and !is_null($iMode)) {
			chmod($sFileDestFullPath,$iMode);
		}
		if(!strpos($sDirDest,'/tmp/avatars/') && !strpos($sDirDest,'/tmp/fotos/')) {
			$sFileDestFullPath = $this->UploadToSelectelStorage($sFileDestFullPath);
		}

		if ($bRemoveSource) {
			@unlink($sFileSource);
		}
		/**
		 * Если копирование прошло успешно, возвращаем новый серверный путь до файла
		 */
		if ($bResult) {
			return $sFileDestFullPath;
		}
		return false;
	}

}

?>