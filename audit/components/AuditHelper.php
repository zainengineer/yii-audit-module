<?php
/**
 * AuditHelper
 *
 * @author Brett O'Donnell <cornernote@gmail.com>
 * @author Zain Ul abidin <zainengineer@gmail.com>
 * @copyright 2013 Mr PHP
 * @link https://github.com/cornernote/yii-audit-module
 * @license BSD-3-Clause https://raw.github.com/cornernote/yii-audit-module/master/LICENSE
 *
 * @package yii-audit-module
 */
class AuditHelper
{

    /**
     * @param $value mixed
     * @return string
     */
    public static function pack($value)
    {
        return gzcompress(serialize($value));
    }

    /**
     * @param $value string
     * @return mixed
     */
    public static function unpack($value)
    {
        return unserialize(gzuncompress($value));
    }


    /**
     * @param $text string
     * @return string
     */
    public static function replaceFileWithAlias($text)
    {
        $aliases = array('audit', 'zii', 'system', 'application', 'ext', 'modules', 'public');
        foreach ($aliases as $alias) {
            $aliasPath = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, Yii::getPathOfAlias($alias));
            if (!$aliasPath)
                continue;
            if (stripos($text, $aliasPath) !== false) {
                $text = str_ireplace($aliasPath, $alias, $text);
            }
        }
        return $text;
    }
    public static function isAdmin()
    {
        $app = Yii::app();
        /** @var AuditModule $audit */
        $audit = $app->getModule('audit');
        if (in_array($app->getUser()->getName(), $audit->adminUsers)){
            return true;
        }
        $isAdmin = false;
        if ($audit->adminCheckCallBack){
            $isAdmin = call_user_func($audit->adminCheckCallBack);
        }
        return $isAdmin;
    }
    public static function modifyErrorEvent(AuditErrorHandler $oErrorInstance, CEvent $oErrorEvent)
    {
        if ($oErrorInstance->phpStormRemote && !empty($oErrorEvent->file) && !empty($oErrorEvent->line)){
            $oErrorEvent->file = self::phpStormFriendly($oErrorEvent->file, $oErrorEvent->line);
        }
    }

    /**
     * To use it you need to overwrite the view
     * from framework/views/exception.php
     * to app/views/system/exception.php
     *
     * And then make sure link is not html escaped
     *
     * @param $vFileName
     * @param $line
     * @return mixed|string
     */
    public static function phpStormFriendly($vFileName, $line)
    {
        $vFileName = self::getBaseName($vFileName);
        $vFileName =  "<a href='http://localhost:8091/?message=$vFileName:$line'>$vFileName:$line</a>";
        return $vFileName;
    }
    public static function getBaseName($vFileName)
    {
        if (isset($_SERVER ['SCRIPT_FILENAME'])) {
            $vFileName = str_replace( dirname(dirname($_SERVER ['SCRIPT_FILENAME'])) . '/', '', $vFileName);
        }
        return $vFileName;
    }

}
