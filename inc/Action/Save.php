<?php

namespace dokuwiki\Action;

use dokuwiki\Action\Exception\ActionAbort;
use dokuwiki\Action\Exception\ActionException;

/**
 * Class Save
 *
 * Save at the end of an edit session
 *
 * @package dokuwiki\Action
 */
class Save extends AbstractAction {

    /** @inheritdoc */
    function minimumPermission() {
        global $INFO;
        if($INFO['exists']) {
            return AUTH_EDIT;
        } else {
            return AUTH_CREATE;
        }
    }

    /** @inheritdoc */
    public function preProcess() {
        if(!checkSecurityToken()) throw new ActionException('preview');

        global $ID;
        global $DATE;
        global $PRE;
        global $TEXT;
        global $SUF;
        global $SUM;
        global $lang;
        global $INFO;
        global $INPUT;

        //spam check
        if(checkwordblock()) {
            msg($lang['wordblock'], -1);
            throw new ActionException('edit');
        }
        //conflict check
        if($DATE != 0 && $INFO['meta']['date']['modified'] > $DATE) {
            throw new ActionException('conflict');
        }

        //save it
        saveWikiText($ID, con($PRE, $TEXT, $SUF, true), $SUM, $INPUT->bool('minor')); //use pretty mode for con
        //unlock it
        unlock($ID);

        //delete draft
        act_draftdel('fixme'); // FIXME replace this utility function
        //session_write_close(); // FIXME close session higher up

        // when done, show page
        throw new ActionAbort();
    }

}