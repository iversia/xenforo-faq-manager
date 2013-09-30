<?php

class Iversia_FAQ_ViewPublic_Permalink extends XenForo_ViewPublic_Base
{
    public function renderHtml()
    {
        $bbCodeParser = new XenForo_BbCode_Parser(XenForo_BbCode_Formatter_Base::create('Base', array('view' => $this)));
        $bbCodeOptions = array(
            'states' => array(
                'viewAttachments' => false
            )
        );

        if (isset($this->_params['question']['answer'])) {

            $this->_params['question']['answer'] = new XenForo_BbCode_TextWrapper($this->_params['question']['answer'], $bbCodeParser);

        }
    }
}
