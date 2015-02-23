<?php

class Denkmal_FormField_Tags extends \CM_FormField_Abstract {

    protected function _initialize() {
        $this->_options['cardinality'] = $this->_params->has('cardinality') ? $this->_params->getInt('cardinality') : null;
    }

    public function validate(\CM_Frontend_Environment $environment, $userInput) {
        $userInput = CM_Params::jsonDecode($userInput);
        $tagList = Functional\map($userInput, function ($tagId) {
            return new Denkmal_Model_Tag($tagId);
        });
        $tagList = Functional\select($tagList, function (Denkmal_Model_Tag $tag) {
            return $this->_getTagListAvailable()->contains($tag);
        });
        if ($this->_options['cardinality'] && count($tagList) > $this->_options['cardinality']) {
            throw new CM_Exception_FormFieldValidation('Too many tags.');
        }
        return $tagList;
    }

    public function prepare(CM_Params $renderParams, CM_Frontend_Environment $environment, CM_Frontend_ViewResponse $viewResponse) {
        $tagList = null !== $this->getValue() ? $this->getValue() : [];
        $tagIdList = Functional\map($tagList, function (Denkmal_Model_Tag $tag) {
            return $tag->getId();
        });

        $viewResponse->set('tagListAvailable', $this->_getTagListAvailable());
        $viewResponse->set('tagIdList', $tagIdList);
        $viewResponse->getJs()->setProperty('tagIdList', $tagIdList);
    }

    /**
     * @return Denkmal_Paging_Tag_Active
     */
    private function _getTagListAvailable() {
        return new Denkmal_Paging_Tag_Active();
    }
}
