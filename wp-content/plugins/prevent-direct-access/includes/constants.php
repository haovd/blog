<?php

if ( ! class_exists('PDA_Lite_Constants') ) {
    class PDA_Lite_Constants {
        const SITE_OPTION_NAME = 'pda_lite_site_options';
        const PREFIX_API_NAME = 'pda-lite/v1';
        const PROTECTION_META_DATA = '_pda_protection';
        const PDA_LITE_FILE_PROTECTED = 'protected';
        const PDA_LITE_FILE_UNPROTECTED = 'unprotected';
        const PDA_LITE_CLASS_FOR_FILE_UNPROTECTED  = 'pda-unprotected';
        const AFFILIATE_PAGE_PREFIX = 'pda-gold';
        const PDA_LITE_TITLE_FOR_FILE_PROTECTED = 'This file is protected';
        const PDA_LITE_TITLE_FOR_FILE_UNPROTECTED = 'This file is unprotected';
        const WARNING_PLAN = '- available in Gold version only';
        const OPTION_NAME = 'FREE_PDA_SETTINGS';
        const PDA_LITE_NO_ACCESS_PAGE = 'search_result_page_404';
        const PDA_LITE_ENABLE_DIRECTORY_LISTING = 'enable_directory_listing';
        const PDA_LITE_ENABLE_IMAGE_HOT_LINKING = 'enable_image_hot_linking';
    }
}