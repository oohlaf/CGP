<?php

require_once 'Default.class.php';

class Type_Udisks extends Type_Default {

	function rrd_gen_graph() {
		$rrdgraph = $this->rrd_options();

		$sources = $this->rrd_get_sources();

		$i=0;
		foreach ($this->tinstances as $tinstance) {
			foreach ($this->data_sources as $ds) {
				$rrdgraph[] = sprintf('DEF:min_%s=%s:%s:MIN', crc32hex($sources[$i]), $this->parse_filename($this->files[$tinstance]), $ds);
				$rrdgraph[] = sprintf('DEF:avg_%s=%s:%s:AVERAGE', crc32hex($sources[$i]), $this->parse_filename($this->files[$tinstance]), $ds);
				$rrdgraph[] = sprintf('DEF:max_%s=%s:%s:MAX', crc32hex($sources[$i]), $this->parse_filename($this->files[$tinstance]), $ds);

				//$rrdgraph[] = sprintf('CDEF:c_thresh_%s=40', crc32hex($sources[$i])); // hardcoded for now

				//$rrdgraph[] = sprintf('VDEF:v_thresh_%s=c_thresh_%s,AVERAGE', crc32hex($sources[$i]), crc32hex($sources[$i])); // Needed?
				$i++;
			}
		}

		$c = 0;
		foreach ($sources as $source) {
			$dsname = empty($this->ds_names[$source]) ? $source : $this->ds_names[$source];
			$color = is_array($this->colors) ? (isset($this->colors[$source])?$this->colors[$source]:$this->colors[$c++]) : $this->colors;

			//current value
			$rrdgraph[] = sprintf('"LINE1:avg_%s#%s:%s"', crc32hex($source), $this->validate_color($color), $this->rrd_escape($dsname));
			$rrdgraph[] = sprintf('"GPRINT:min_%s:MIN:%s Min,"', crc32hex($source), $this->rrd_format);
			$rrdgraph[] = sprintf('"GPRINT:avg_%s:AVERAGE:%s Avg,"', crc32hex($source), $this->rrd_format);
			$rrdgraph[] = sprintf('"GPRINT:max_%s:MAX:%s Max,"', crc32hex($source), $this->rrd_format);
			$rrdgraph[] = sprintf('"GPRINT:avg_%s:LAST:%s Last\\l"', crc32hex($source), $this->rrd_format);
			//$rrdgraph[] = sprintf('"GPRINT:v_thresh_%s:LAST:%s Thresh\\l"', crc32hex($source), $this->rrd_format);
		}

		return $rrdgraph;
	}
}

?>
