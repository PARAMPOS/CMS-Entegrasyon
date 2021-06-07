<?php
/**
 * Protect directory
 *
 * @version   3.4.0
 * @author    Param www.param.com.tr
 * @license   http://opensource.org/licenses/MIT MIT
 */

header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');

header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');

header('Location: ../');
exit;