<?php
namespace ReportPortal\Basic\Enum;

/**
 * Enum describes report portal items' types.
 *
 * @author Mikalai_Kabzar
 */
class ItemTypesEnum
{
    const SUITE = 'SUITE';

    const STORY = 'STORY';

    const TEST = 'TEST';

    const SCENARIO = 'SCENARIO';

    const STEP = 'STEP';

    const BEFORE_CLASS = 'BEFORE_CLASS';

    const BEFORE_GROUPS = 'BEFORE_GROUPS';

    const BEFORE_METHOD = 'BEFORE_METHOD';

    const BEFORE_SUITE = 'BEFORE_SUITE';

    const BEFORE_TEST = 'BEFORE_TEST';

    const AFTER_CLASS = 'AFTER_CLASS';

    const AFTER_GROUPS = 'AFTER_GROUPS';

    const AFTER_METHOD = 'AFTER_METHOD';

    const AFTER_SUITE = 'AFTER_SUITE';

    const AFTER_TEST = 'AFTER_TEST';
}