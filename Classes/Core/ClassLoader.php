<?php
namespace F3\FLOW3\Core;

/*                                                                        *
 * This script belongs to the FLOW3 framework.                            *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License as published by the *
 * Free Software Foundation, either version 3 of the License, or (at your *
 * option) any later version.                                             *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser       *
 * General Public License for more details.                               *
 *                                                                        *
 * You should have received a copy of the GNU Lesser General Public       *
 * License along with the script.                                         *
 * If not, see http://www.gnu.org/licenses/lgpl.html                      *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

/**
 * Class Loader implementation which loads .php files found in the classes
 * directory of an object.
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @proxy disable
 * @scope singleton
 */
class ClassLoader {

	/**
	 * @var \F3\FLOW3\Cache\Frontend\PhpFrontend
	 */
	protected $classesCache;

	/**
	 * An array of \F3\FLOW3\Package\Package objects
	 * @var array
	 */
	protected $packages = array();

	/**
	 * Injects the cache for storing the renamed original classes
	 *
	 * @param \F3\FLOW3\Cache\Frontend\PhpFrontend $classesCache
	 * @return void
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function injectClassesCache(\F3\FLOW3\Cache\Frontend\PhpFrontend $classesCache) {
		$this->classesCache = $classesCache;
	}

	/**
	 * Loads php files containing classes or interfaces found in the classes directory of
	 * a package and specifically registered classes.
	 *
	 * @param string $className Name of the class/interface to load
	 * @return void
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function loadClass($className) {
		if ($this->classesCache !== NULL) {
			$this->classesCache->requireOnce(str_replace('\\', '_', $className));
			if (class_exists($className, FALSE)) {
				return TRUE;
			}
		}

		$classNameParts = explode('\\', $className);
		if (is_array($classNameParts) && $classNameParts[0] === 'F3' && isset($this->packages[$classNameParts[1]])) {
			if ($classNameParts[2] === 'Tests' && $classNameParts[3] === 'Functional') {
				$classFilePathAndName = $this->packages[$classNameParts[1]]->getFunctionalTestsPath();
				$classFilePathAndName .= implode(array_slice($classNameParts, 4, -1), '/') . '/';
				$classFilePathAndName .= end($classNameParts) . '.php';
			} else {
				$classFilePathAndName = $this->packages[$classNameParts[1]]->getClassesPath();
				$classFilePathAndName .= implode(array_slice($classNameParts, 2, -1), '/') . '/';
				$classFilePathAndName .= end($classNameParts) . '.php';
			}
		}

		if (!isset($classFilePathAndName) && $this->packages === array() && $classNameParts[0] === 'F3' && $classNameParts[1] === 'FLOW3') {
			$classFilePathAndName = FLOW3_PATH_FLOW3 . 'Classes/';
			$classFilePathAndName .= implode(array_slice($classNameParts, 2, -1), '/') . '/';
			$classFilePathAndName .= end($classNameParts) . '.php';
		}

		if (isset($classFilePathAndName) && file_exists($classFilePathAndName)) {
			require($classFilePathAndName);
		}
	}

	/**
	 * Sets the available packages
	 *
	 * @param array $packages An array of \F3\FLOW3\Package\Package objects
	 * @return void
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function setPackages(array $packages) {
		$this->packages = $packages;
	}

}

?>