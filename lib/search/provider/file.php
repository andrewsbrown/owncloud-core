<?php
/**
 * ownCloud
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE
 * License as published by the Free Software Foundation; either
 * version 3 of the License, or any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU AFFERO GENERAL PUBLIC LICENSE for more details.
 *
 * You should have received a copy of the GNU Affero General Public
 * License along with this library.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace OC\Search\Provider;

/**
 * Provide search results from the 'files' app
 */
class File extends \OC\Search\Provider{
    
        /**
         * Search for files and folders matching the given query
         * @param string $query
         * @return \OC\Search\Result
         */
        function search($query) {
            $files = \OC\Files\Filesystem::search($query);
            $results = array();
            // edit results
            foreach ($files as $fileData) {
                // skip versions
                if (strpos($fileData['path'], '_versions') === 0) {
                    continue;
                }
                // skip top-level folder
                if ($fileData['name'] == 'files' && $fileData['parent'] == -1) {
                    continue;
                }
                // create folder result
                if($fileData['mimetype'] == 'httpd/unix-directory'){
                    $result = new \OC\Search\Result\Folder($fileData);
                }
                // or create file result
                else{
                    $result = new \OC\Search\Result\File($fileData);
                }
                // add to results
                $results[] = $result;
            }
            // return
            return $results;
        }
        
        /**
         * 
         * @param type $query
         * @return \OC_Search_Result
         * @deprecated
         */
	function old_search($query) {
		$files=\OC\Files\Filesystem::search($query, true);
		$results=array();
		$l=OC_L10N::get('lib');
		foreach($files as $fileData) {
			$path = $fileData['path'];
			$mime = $fileData['mimetype'];

			$name = basename($path);
			$text = '';
			$skip = false;
			if($mime=='httpd/unix-directory') {
				$link = OC_Helper::linkTo( 'files', 'index.php', array('dir' => $path));
				$type = (string)$l->t('Files');
			}else{
				$link = OC_Helper::linkToRoute( 'download', array('file' => $path));
				$mimeBase = $fileData['mimepart'];
				switch($mimeBase) {
					case 'audio':
						$skip = true;
						break;
					case 'text':
						$type = (string)$l->t('Text');
						break;
					case 'image':
						$type = (string)$l->t('Images');
						break;
					default:
						if($mime=='application/xml') {
							$type = (string)$l->t('Text');
						}else{
							$type = (string)$l->t('Files');
						}
				}
			}
			if(!$skip) {
				$results[] = new \OC_Search_Result($name, $text, $link, $type);
			}
		}
		return $results;
	}
}
