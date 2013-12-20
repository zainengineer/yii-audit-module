<?php

/**
 * AuditWebController
 *
 * @property AuditModule $module
 *
 * @author Brett O'Donnell <cornernote@gmail.com>
 * @author Zain Ul abidin <zainengineer@gmail.com>
 * @copyright 2013 Mr PHP
 * @link https://github.com/cornernote/yii-audit-module
 * @license BSD-3-Clause https://raw.github.com/cornernote/yii-audit-module/master/LICENSE
 *
 * @package yii-audit-module
 */
class AuditWebController extends CController
{

    /**
     * @var array context menu items. This property will be assigned to {@link CMenu::items}.
     */
    public $menu = array();

    /**
     * @var array breadcrumbs links to current page. This property will be assigned to {@link CBreadcrumbs::links}.
     */
    protected $_breadcrumbs = array();

    /**
     * @var
     */
    protected $_pageHeading;

    /**
     * @var
     */
    protected $_loadModel;

    /**
     * @return string Defaults to the controllers pageTitle.
     */
    public function getPageHeading()
    {
        if ($this->_pageHeading === null)
            $this->_pageHeading = $this->pageTitle;
        return $this->_pageHeading;
    }

    /**
     * @param $pageHeading string
     */
    public function setPageHeading($pageHeading)
    {
        $this->_pageHeading = $pageHeading;
    }

    /**
     * @return string
     */
    public function getBreadcrumbs()
    {
        if ($this->_breadcrumbs === null)
            $this->_breadcrumbs = $this->pageTitle;
        return $this->_breadcrumbs;
    }

    /**
     * @param string $breadcrumbs
     */
    public function setBreadcrumbs($breadcrumbs)
    {
        $this->_breadcrumbs = $breadcrumbs;
    }

    /**
     * @param string $name
     * @param array|string $link
     */
    public function addBreadcrumb($name, $link = null)
    {
        if ($link)
            $this->_breadcrumbs[$name] = $link;
        else
            $this->_breadcrumbs[] = $name;
    }

    /**
     * Loads a CActiveRecord or throw a CHTTPException
     *
     * @param $id
     * @param bool|string $model
     * @return CActiveRecord
     * @throws CHttpException
     */
    public function loadModel($id, $model = false)
    {
        if (!$model)
            $model = str_replace('Controller', '', get_class($this));
        if ($this->_loadModel === null) {
            $this->_loadModel = CActiveRecord::model($model)->findbyPk($id);
            if ($this->_loadModel === null)
                throw new CHttpException(404, Yii::t('audit', 'The requested page does not exist.'));
        }
        return $this->_loadModel;
    }

    /**
     * @return string
     */
    public function renderBreadcrumbs()
    {
        $breadcrumbs = $this->getBreadcrumbs();
        if (!$breadcrumbs)
            return '';
        $this->addBreadcrumb($this->pageHeading);
        return $this->widget('zii.widgets.CBreadcrumbs', array(
            'links' => $this->getBreadcrumbs(),
            'homeLink' => false,
        ), true);
    }

}