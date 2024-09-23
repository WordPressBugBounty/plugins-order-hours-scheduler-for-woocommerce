<?php

namespace Zhours;

defined( 'ABSPATH' ) || exit;

class Addons
{
		const ORDER_HOURS_WIDGET_NAMESPACE = "ZZHoursWidget";
		const ORDER_DELIVERY_NAMESPACE = "ZZHoursDelivery";

		public static function is_active_add_on( $namespace )
		{
				$name = "\\{$namespace}\\ACTIVE";
				return defined($name) && constant($name);
		}
}
