<?php
class Hs_Minifier_Helper_Core_Data extends Mage_Core_Helper_Data
{
	/**
	 * Hs EDIT - added call for JS minimizing
	 * @param array $srcFiles
	 * @param bool $targetFile
	 * @param bool $mustMerge
	 * @param null $beforeMergeCallback
	 * @param array $extensionsFilter
	 * @return bool|string
	 */
	public function mergeFiles(array $srcFiles, $targetFile = false, $mustMerge = false, $beforeMergeCallback = null, $extensionsFilter = array())
	{
		try {
			// check whether merger is required
			$shouldMerge = $mustMerge || !$targetFile;
			if (!$shouldMerge) {
				if (!file_exists($targetFile)) {
					$shouldMerge = true;
				} else {
					$targetMtime = filemtime($targetFile);
					foreach ($srcFiles as $file) {
							if (!file_exists($file) || @filemtime($file) > $targetMtime) {
									$shouldMerge = true;
									break;
							}
					}
				}
			}

			// merge contents into the file
			if ($shouldMerge) {
				if ($targetFile && !is_writeable(dirname($targetFile))) {
					// no translation intentionally
					throw new Exception(sprintf('Path %s is not writeable.', dirname($targetFile)));
				}

				// filter by extensions
				if ($extensionsFilter) {
					if (!is_array($extensionsFilter)) {
						$extensionsFilter = array($extensionsFilter);
					}
					if (!empty($srcFiles)){
						foreach ($srcFiles as $key => $file) {
							$fileExt = strtolower(pathinfo($file, PATHINFO_EXTENSION));
							if (!in_array($fileExt, $extensionsFilter)) {
								unset($srcFiles[$key]);
							}
						}
					}
				}
				if (empty($srcFiles)) {
					// no translation intentionally
					throw new Exception('No files to compile.');
				}

				$data = '';
				foreach ($srcFiles as $file) {
					if (!file_exists($file)) {
						continue;
					}
					$contents = file_get_contents($file) . "\n";
					if ($beforeMergeCallback && is_callable($beforeMergeCallback)) {
						$contents = call_user_func($beforeMergeCallback, $file, $contents);
					}
					$data .= $contents;
				}
				if (!$data) {
					// no translation intentionally
					throw new Exception(sprintf("No content found in files:\n%s", implode("\n", $srcFiles)));
				}
				if ($targetFile) {
					/** Hs EDIT START **/
					if(isset($file)){
						// Minimize only .js files
						$fileExt = strtolower(pathinfo($file, PATHINFO_EXTENSION));
						if(Mage::getStoreConfigFlag('dev/js/minimize_js') && $fileExt === 'js'){
								$data = $this->minimizeJs($data);
						}
					}
					/** Hs EDIT END **/
					file_put_contents($targetFile, $data, LOCK_EX);
				} else {
					return $data; // no need to write to file, just return data
				}
			}

			return true; // no need in merger or merged into file successfully
		} catch (Exception $e) {
			Mage::logException($e);
		}
		return false;
	}

	/**
	 * Hs - main JS minimizer function
	 * @param $data
	 * @return bool|string
	 */
	public function minimizeJs($data)
	{
		$minifer = Mage::helper('minifier/minifier');
		$result = $minifer->minify($data);

		if($result !== false) {
				return $result;
		}
		return $data;
	}
}