<?php

namespace App\Console\Commands;

use DB;
use Illuminate\Console\Command;

class createModel extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'model:create '
			. '{table : database table name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create methods of a model from table and echo them. '
			. 'Use model:create {table} >> model.txt to create a file';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $table = $this->argument('table');
		$this->makeModelMethods($table);
    }

	private function makeModelMethods($table) {
		$sql = "SELECT *  FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name=? AND TABLE_SCHEMA=?";
		$cols = \Illuminate\Support\Facades\DB::select($sql, [$table, env('DB_DATABASE')]);
//		var_export($cols);
		echo PHP_EOL;
		echo "\t".'protected $table = \''.$table.'\';'."\n";
		echo "\t".'protected $primaryKey = \'id\';'."\n";
		$timestamps = 0;
		foreach($cols as $col) {
			$name = $col->COLUMN_NAME;
			if ($name == 'created_at' || $name == 'updated_at') $timestamps++;
			if ($timestamps == 2) break;
		}
		if ($timestamps < 2)
			echo "\t".'public $timestamps = false;'."\n";

		echo "//\t".'protected $guarded = [];'."\n";
		echo "//\t".'protected $fillable = [];'."\n";

		echo PHP_EOL;
		$colsAdded = [];
		$getSets = "";
		$scopes = [];
		foreach($cols as $col) {
			$name = $col->COLUMN_NAME;
			if (in_array($name, $colsAdded)) continue; // Weird that they are double
			$colsAdded[] = $name;
			$name2 = $name;
			if ($name == 'id')
				$name2 = preg_replace("/s$/", '', $table).'_'.$name;
			$scopes[] = $name;
			$get = $col->DATA_TYPE == 'int' && $col->IS_NULLABLE=='NO' ? 'intval($this->'.$name.')' : '$this->'.$name;
			$getSets .= "\tpublic function get".str_replace(' ', '', ucwords(str_replace('_', ' ', $name2)))."() {\n"
					."\t\t".'return '.$get.';'."\n"
					."\t}\n\n";
			if ($name == 'created_at' || $name == 'updated_at' || $name == 'id') continue;
			$getSets .= "\tpublic function set".str_replace(' ', '', ucwords(str_replace('_', ' ', $name2))).'($value)'." {\n";
			if ($col->DATA_TYPE == 'text') {
				$getSets .= "\t\t".'if (is_array($value)) $value = json_encode($value);'.PHP_EOL;
			}
			$getSets .= "\t\t".'$this->'.$name.' = $value;'."\n"
					."\t}\n\n";
		}

		foreach ($colsAdded as $col) {
			echo sprintf("\tconst COL_%s = '%s';", strtoupper($col), $col) . PHP_EOL;
		}
		echo PHP_EOL;

		$this->printSectionHeader('Eloquent Scopes');

		foreach ($colsAdded as $col) {
//			if (in_array($col, ['id', 'player_id', 'team_id', 'type', 'status', 'season', 'user_id', 'club_id'])) {
			if (in_array($col, ['id', 'type', 'status', 'season']) || substr($col, -3) == '_id') {
				echo $this->makeScope($col, 'by', '=');

			} else if (strpos($col, 'time') !== false) {
				echo $this->makeScope($col, 'from', '>=');
				echo $this->makeScope($col, 'to', '<');
			}
		}

		$this->printSectionHeader('GET / SET');
		echo $getSets;
	}

	private function printSectionHeader($text) {
		echo "\t/*" . PHP_EOL;
		echo sprintf("\t * %s", $text) . PHP_EOL;
		echo "\t */" . PHP_EOL . PHP_EOL;
	}
	private function makeScope($col, $prefix, $equal) {
		$txt = "";
		$colname = str_replace(' ', '', ucwords(str_replace('_', ' ', $col)));
		$txt .= sprintf("\tpublic function scope%s%s(\$query, \$val) {", ucfirst($prefix), $colname) . PHP_EOL;
		$txt .= sprintf("\t\t\$query->where('%s'", $col);
		if ($equal && $equal != '=') $txt .= sprintf(", '%s'", $equal);
		$txt .= ', $val);' . PHP_EOL;
		$txt .= "\t}" . PHP_EOL . PHP_EOL;
		return $txt;
	}
}