<?php

/*
	Phoronix Test Suite
	URLs: http://www.phoronix.com, http://www.phoronix-test-suite.com/
	Copyright (C) 2009 - 2010, Phoronix Media
	Copyright (C) 2009 - 2010, Michael Larabel

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 3 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program. If not, see <http://www.gnu.org/licenses/>.
*/

class sys_temp extends phodevi_sensor
{
	public static function get_type()
	{
		return "sys";
	}
	public static function get_sensor()
	{
		return "temp";
	}
	public static function get_unit()
	{
		return "Celsius";
	}
	public static function support_check()
	{
		$test = self::read_sensor();
		return is_numeric($test) && $test != -1;
	}
	public static function read_sensor()
	{
		// Reads the system's temperature
		$temp_c = -1;

		if(IS_LINUX)
		{
			$raw_temp = phodevi_linux_parser::read_sysfs_node("/sys/class/hwmon/hwmon*/device/temp3_input", "POSITIVE_NUMERIC", array("name" => "!coretemp"));

			if($raw_temp == -1)
			{
				$raw_temp = phodevi_linux_parser::read_sysfs_node("/sys/class/hwmon/hwmon*/device/temp2_input", "POSITIVE_NUMERIC", array("name" => "!coretemp"));
			}

			if($raw_temp == -1)
			{
				$raw_temp = phodevi_linux_parser::read_sysfs_node("/sys/class/hwmon/hwmon*/device/temp1_input", "POSITIVE_NUMERIC", array("name" => "!coretemp"));
			}

			if($raw_temp == -1)
			{
				$raw_temp = phodevi_linux_parser::read_sysfs_node("/sys/class/hwmon/hwmon*/temp1_input", "POSITIVE_NUMERIC");
			}

			if($raw_temp != -1)
			{
				if($raw_temp > 1000)
				{
					$raw_temp = $raw_temp / 1000;
				}

				$temp_c = pts_math::set_precision($raw_temp, 2);	
			}

			if($temp_c == -1)
			{
				$acpi = phodevi_linux_parser::read_acpi(array(
					"/thermal_zone/THM1/temperature",
					"/thermal_zone/TZ00/temperature",
					"/thermal_zone/TZ01/temperature"), "temperature");

				if(($end = strpos($acpi, ' ')) > 0)
				{
					$temp_c = substr($acpi, 0, $end);
				}
			}

			if($temp_c == -1)
			{
				$sensors = phodevi_linux_parser::read_sensors(array("Sys Temp", "Board Temp"));

				if($sensors != false && is_numeric($sensors))
				{
					$temp_c = $sensors;
				}
			}
		}
		else if(IS_BSD)
		{
			$acpi = phodevi_bsd_parser::read_sysctl("hw.acpi.thermal.tz1.temperature");

			if(($end = strpos($acpi, 'C')) > 0)
			{
				$acpi = substr($acpi, 0, $end);

				if(is_numeric($acpi))
				{
					$temp_c = $acpi;
				}
			}
		}

		return $temp_c;
	}
}

?>