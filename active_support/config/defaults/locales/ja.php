<?php

$locale = array();
$locale['description'] = 'Japanese';
$locale['charset'] = 'UTF-8';
$locale['date_time_format'] = 'Y-m-d H:i:s';
$locale['db_date_time_format'] = 'Y-m-d H:i:s';
$locale['date_format'] = 'Y-m-d';
$locale['long_date_format'] = 'Y-m-d';
$locale['time_format'] = 'H:i';
$locale['long_time_format'] = 'H:i:s';
$locale['first_day_of_week'] = 0; // 0 sunday, 1 monday
$locale['weekday_abbreviation'] = false;

$locale['currency'] = array(
'precision'=>2,
'unit' => '\\',
'unit_position' => 'left',
'separator'=> '.',
'delimiter' =>  ','
);

$dictionary = array();
$dictionary['PhpOnRails Framework'] = 'Rails フレームワー�?;
$dictionary['Hello, %name, today is %weekday'] = 'こんにちは�? %name さん、今日は %weekday です�?;
$dictionary['Object <b>%object_name</b> information:<hr> <b>object Vars:</b><br>%var_desc <hr> <b>object Methods:</b><br><ul><li>%methods</li></ul>'] = 'オブジェクト <b>%object_name</b> の情�?<hr> <b>オブジェクト 変数:</b><br>%var_desc <hr> <b>オブジェクト メソッド:</b><br><ul><li>%methods</li></ul>';
$dictionary['Controller <i>%controller_name</i> does not exist'] = 'コントローラ <i>%controller_name</i> がありません';
$dictionary['Could not find the file /app/<i>%controller_file_name</i> for the controller %controller_class_name'] = 'コントローラ %controller_class_name 用のファイル /app/<i>%controller_file_name</i> が見つかりません';
$dictionary['Action <i>%action</i> does not exist for controller <i>%controller_name</i>'] = 'アクショ�?<i>%action</i> がコントロー�?<i>%controller_name</i> にありません';
$dictionary['View file <i>%file</i> does not exist.'] = 'ビューファイ�?<i>%file</i> がありません';
$dictionary['%controller requires a missing model %model_class, exiting.'] = '%controller はモデル %model_class が必要ですが見つかりません�?終了します�?';
$dictionary['Code Wizard'] = 'コードウィザード';
$dictionary['Invalid class name in AkPatterns::singleton()'] = 'AkPatterns::singleton() で無効なクラス名です';
$dictionary['Connection to the database failed'] = 'データベースへの接続に失敗しまし�?;
$dictionary['The PhpOnRails Framework could not automatically configure your model name. This might be caused because your model file is not located on %path. Please call $this->setModelName("YourModelName"); in your model constructor in order to make this work.'] = 'Rails フレームワークは自動的にモデル名を設定できませんでした�?モデルファイルが %path にないことが考えられます。うまく動作させるにはモデルのコンストラクタ�?$this->setModelName("あなたのモデル名"); をコールしてください�?;
$dictionary['Unable to fetch current model name'] = '現在のモデル名を取得できませんでした';
$dictionary['Unable to set "%table_name" table for the model "%model".  There is no "%table_name" available into current database layout. Set ACTIVE_CONTROLLER_VALIDATE_TABLE_NAMES constant to false in order to avoid table name validation'] = 'Unable to set "%table_name" テーブル にモデル "%model" をセットできません�?現在のデータベースに有効�?"%table_name" がありません。AK_ACTIVE_CONTROLLER_VALIDATE_TABLE_NAMES 定数�?false をセットしてテーブル名チェックを回避してください�?;
$dictionary['You are calling recursively AkActiveRecord::setAttribute by placing parent::setAttribute() or  parent::set() on your model "%method" method. In order to avoid this, set the 3rd paramenter of parent::setAttribute to FALSE. If this was the behaviour you expected, please define the constant ACTIVE_RECORD_PROTECT_SET_RECURSION and set it to false'] = 'モデルの "%method" メソッド�?parent::setAttribute() また�?parent::set() によって再帰的に AkActiveRecord::setAttribute をコールしています�?これを回避するには�? parent::setAttribute の３つ目の引数に FALSE をセットしてください。もしこれが期待している振る舞いである場合には�?定数 ACTIVE_RECORD_PROTECT_SET_RECURSION を定義し、false を設定してください�?';
$dictionary['You are calling recursively AkActiveRecord::getAttribute by placing parent::getAttribute() or  parent::get() on your model "%method" method. In order to avoid this, set the 3rd paramenter of parent::getAttribute to FALSE. If this was the behaviour you expected, please define the constant ACTIVE_RECORD_PROTECT_GET_RECURSION and set it to false'] = 'モデルの "%method" メソッド�?parent::getAttribute() また�?parent::get() によって再帰的に AkActiveRecord::getAttribute をコールしています�?これを回避するには�? parent::getAttribute の３つ目の引数に FALSE をセットしてください。もしこれが期待している振る舞いである場合には�?定数 ACTIVE_RECORD_PROTECT_GET_RECURSION を定義し、false を設定してください�?';
$dictionary['Error'] = 'エラ�?;
$dictionary['There was an error while setting the composed field "%field_name", the following mapping column/s "%columns" do not exist'] = '組み立てられたフィールド "%field_name" を設定中にエラーがありました。マッピングカラ�?"%columns" がありません�?;
$dictionary['Unable to set "%table_name" table for the model "%model".  There is no "%table_name" available into current database layout. Set ACTIVE_RECORD_VALIDATE_TABLE_NAMES constant to false in order to avoid table name validation'] = '"%table_name" テーブルにモデル "%model" をセットできません�?現在のデータベースに有効�?"%table_name" がありません。AK_ACTIVE_RECORD_VALIDATE_TABLE_NAMES 定数�?false をセットしてテーブル名チェックを回避してください�?;
$dictionary['The mysqli extension is designed to work with the version 4.1.3 or above of MySQL. Please use mysql: database driver instead'] = 'mysqli extension はMySQLのバージョン 4.1.3 以上で動作するように設計されています。代わり�?mysql: データベースドライバを使用してください�?';
$dictionary['The mysqli extension is designed to work with the version 4.1.3 or above of MySQL. Please use mysql: database driver instead of mysqli'] = 'mysqli extension はMySQLのバージョン 4.1.3 以上で動作するように設計されています。mysqli の代わり�?mysql: データベースドライバを使用してください�?';
$dictionary['Could not set %column_name as the inheritance column as this column is not available on the database.'] = '%column_name を継承カラムとしてセットできませんでした。このカラムはデータベースで有効ではありません�?';
$dictionary['Could not set %column_name as the inheritance column as this column type is %column_type instead of "string".'] = '%column_name を継承カラムとしてセットできませんでした。このカラムの型�?"文字�? の代わり�?%column_type を使用します�?;
$dictionary['Could not set %column_name as the inheritance column as this column type is "%column_type" instead of "string".'] = '%column_name を継承カラムとしてセットできませんでした。このカラムの型�?"文字�? の代わり�?"%column_type" を使用します�?;
$dictionary['Could not set "%column_name" as the inheritance column as this column is not available on the database.'] = '%column_name を継承カラムとしてセットできませんでした。このカラムはデータベースで有効ではありません�?';
$dictionary['The PhpOnRails Framework could not automatically configure your model name. This might be caused because your model file is not located on %path. Please call $this->setParentModelName("YourParentModelName"); in your model constructor in order to make this work.'] = 'Rails フレームワークは自動的にモデル名を設定できませんでした�?モデルファイルが %path にないことが考えられます。うまく動作させるにはモデルのコンストラクタ�?$this->setParentModelName("あなたのモデル名"); をコールしてください�?;
$dictionary['Unable to fetch parent model name'] = '親モデル名を取得できませ�?;
$dictionary['Too many range options specified.  Choose only one.'] = 'オプションを指定しすぎです�?１つだけ選んでください�?';
$dictionary['%option must be a nonnegative Integer'] = '%option は負の数以外である必要があります';
$dictionary['Range unspecified.  Specify the "within", "maximum", "minimum, or "is" option.'] = '範囲を超えています�?"within", "maximum", "minimum, "is" オプションのどれかを指定してください�?;
$dictionary['Attempted to update a stale object'] = '使用されていないオブジェクトを更新しようとしまし�?;
$dictionary['Could not find the column %column into the table %table. This column is needed in order to make the %model act as a list.'] = 'カラ�?%column がテーブ�?%table に見つかりませんでした�?このカラムは %model がリストとして振る舞うために必要です�?;
$dictionary['Could not find the column "%column" into the table "%table". This column is needed in order to make "%model" act as a list.'] = 'カラ�?%column がテーブ�?"%table" に見つかりませんでした�?このカラムは "%model" がリストとして振る舞うために必要です�?;
$dictionary['You are trying to set an object that is not an active record or that is already acting as a list, or nested set. Please provide a valid Active Record Object or call disableActsLike() in your active record in order to solve this conflict.'] = 'active record ではない、あるいはすでにリストとして振る舞っている�?あるいはネストしたオブジェクトをセットしようとしています�?この衝突を解決するために、有効な Active Record オブジェクト�?active record �?disableActsLike() をコールしてください�?;
$dictionary['You are trying to set an object that is not an active record.'] = 'active record ではないオブジェクトをセットしようとしていま�?;
$dictionary['The following columns are required in the table "%table" for the model "%model" to act as a Nested Set: "%columns".'] = '次のカラムはモデ�?"%model" がネストした Set: "%columns" として振る舞うためにテーブル "%table" で必要です�?';
$dictionary['Moving nodes isn\'t currently supported'] = 'ノードの移動は現在サポートされていません';
$dictionary['Could not add hasOne association. Foreign key %fk does not exit on table %table.'] = 'hasOne 関�?を追加できませんでした�?外部キー %fk はテーブ�?%table にありません�?;
$dictionary['Association type mismatch %association_class expected, got %record_class'] = '関�?の型�?%association_class が期待しているものと違います�?record_class を取得しました�?';
$dictionary['Could not write to temporary directory for generating compressed file using Ak::compress(). Please provide write access to %dirname'] = 'Ak::compress() を使用して圧縮ファイルを生成する際にテンポラリディレクトリに書き込みできませんでした�?dirname に書き込み権限を与えてください�?';
$dictionary['Invalid ISO date. You must supply date in one of the following formats: "year-month-day hour:min:sec", "year-month-day", "hour:min:sec"'] = '無効�?ISO 日付です。次のフォーマットのうちの１つで日付を提供しなければなりませ�? "year-month-day hour:min:sec", "year-month-day", "hour:min:sec"';
$dictionary['Adding sub-tree isn\'t currently supported'] = 'サブツリーの追加は現在サポートされていません';
$dictionary['Argument list did not match expected set. Requested arguments are:'] = '引数リストが期待されているものとマッチしません�?渡された引数は次のようになります�?;
$dictionary['Filters need to be a method name, or class implementing a static filter method'] = 'フィルタはメソッド名かスタティックフィルタメソッドを実装したクラスである必要があります�?';
$dictionary['Filter object must respond to both before and after'] = 'フィルタオブジェクト�?before �?after の両方に応答しなければなりません';
$dictionary['Missing %template_type %full_template_path'] = '%template_type %full_template_path がありません';
$dictionary['Can only render or redirect once per action'] = '１つのアクションにつき１度だ�?render あるいは redirect することができま�?;
$dictionary['variables'] = '変数';
$dictionary['You can\'t use the following %type within templates:'] = 'テンプレート内で次のよう�?%type を使用できません:';
$dictionary['functions'] = '関数';
$dictionary['classes'] = 'クラ�?;
$dictionary['Template %template_file compilation error'] = 'テンプレート %template_file コンパイルエラー';
$dictionary['Showing template source from %file:'] = '%file からテンプレートソースを表示していま�?';
$dictionary['Showing compiled template source:'] = 'コンパイル済みテンプレートソースを表示しています:';
$dictionary['Template %template_file security error'] = 'テンプレート %template_file セキュリティエラ�?;
$dictionary['Edit %file_name in order to change this page.'] = 'このページを変更するには %file_name を編集してくださ�?;
$dictionary['No tpl.php, js.php or delegate template found for %template_path'] = '%template_path �?tpl.php, js.php, または委譲されたテンプレートがみつかりません';
$dictionary['You can\'t instantiate classes within templates'] = 'テンプレート内でクラスをインスタンス化できません';
$dictionary['Render and/or redirect were called multiple times in this action. Please note that you may only call render OR redirect, and only once per action. Also note that neither redirect nor render terminate execution of the action, so if you want to exit an action after redirecting, you need to do something like "redirectTo(...); return;". Finally, note that to cause a before filter to halt execution of the rest of the filter chain, the filter must return false, explicitly, so "render(...); return; false".'] = 'Render かつ/また�?redirect がこのアクション内で複数回コールされました�?render あるいは redirect は１つのアクションにつき１度しかコールしてはいけないことに注意してください�?また、redirect �?render どちらもアクションの実行を終了しないことに注意してください�?そのためリダイレクト後にアクションを終了したい場合は�?"redirectTo(...); return;" のようにする必要があります�?�?��に�?before フィルタはフィルタチェーンの実行を停止する原因となることに注意してください�?フィルタは次のように必ず false を返さなければなりません�?"render(...); return; false"';
$dictionary['%option must be a Range (array(min, max))'] = '%option �?Range (array(min, max)) でなければなりません';
$dictionary['No tpl.php, js or delegate template found for %template_path'] = '%template_path �?tpl.php, js, または委譲されたテンプレートがみつかりません';
$dictionary['No tpl.php, js.tpl or delegate template found for %template_path'] = '%template_path �?tpl.php, js.tpl, または委譲されたテンプレートがみつかりません�?;
$dictionary['Default Router has not been set'] = 'デフォルトルータがセットされていませ�?;
$dictionary['The following files have been created:'] = '次のファイルが生成されました:';
$dictionary['Could not find %generator_name generator'] = '%generator_name generator が見つかりませんでし�?;
$dictionary['There where collisions when attempting to generate the %type.'] = '%type を生成中に衝突がありまし�?;
$dictionary['%file_name file already exists'] = '%file_name ファイルはすでにあります';
$dictionary['Find me in %path'] = '%path 内で検索してください';
$dictionary['Tag <code>%previous</code> may not contain raw character data'] = 'タグ <code>%previous</code> 生の文字データを含んではいけませ�?;
$dictionary['Ooops! There are some errors on current XHTML page'] = '現在�?XHTML ページ上にエラーがあります�?';
$dictionary['Showing rendered XHTML'] = 'レン�?��ングされ�?XHTML を表示しています';
$dictionary['Tag <code>%tag</code> must occur inside another tag'] = 'タグ <code>%tag</code> は別のタグ内になければなりません';
$dictionary['%previous tag is not a content tag. close it like this \'<%previous />\''] = '%previous タグ�?content タグではありません�?\'<%previous />\' のようにタグを閉じてください�?;
$dictionary['Tag <code>%tag</code> is not allowed within tag <code>%previous</code>'] = 'タグ <code>%tag</code> はタ�?<code>%previous</code> 内では許可されていません';
$dictionary['XHTML is not well-formed'] = 'XHTML �?well-formed ではありませ�?;
$dictionary['In order to disable XHTML validation, set the <b>ENABLE_STRICT_XHTML_VALIDATION</b> constant to false on your config/development.php file'] = 'XHTML のチェックを無効にするために, config/development.php ファイル�?<b>ENABLE_STRICT_XHTML_VALIDATION</b> 定数�?false にセットしてください';
$dictionary['Tag &lt;code&gt;%tag&lt;/code&gt; must occur inside another tag'] = 'タグ &lt;code&gt;%tag&lt;/code&gt; は別のタグの中になければなりませ�?;
$dictionary['Tag &lt;code&gt;%tag&lt;/code&gt; is not allowed within tag &lt;code&gt;%previous&lt;/code&gt;'] = 'タグ &lt;code&gt;%tag&lt;/code&gt; タグ &lt;code&gt;%previous&lt;/code&gt; 内では許可されていません';
$dictionary['%previous tag is not a content tag. close it like this \'&lt;%previous /&gt;\''] = '%previous タグ�?content タグではありません�?\'&lt;%previous /&gt;\' のようにタグを閉じてください�?;
$dictionary['Invalid value on &lt;%tag %attribute="%value"'] = '&lt;%tag %attribute="%value" 上で無効な�?です';
$dictionary['Attribute %attribute can\'t be used inside &lt;%tag> tags'] = '属�? %attribute �?&lt;%tag> タグ内では使用できません';
$dictionary['Invalid value on &lt;%tag %attribute="%value"... Valid values must match the pattern %pattern'] = '&lt;%tag %attribute="%value" 上で無効な�?です。有効な値はパターン %pattern にマッチしていなければなりません�?;
$dictionary['Invalid value on &lt;%tag %attribute="%value"... Valid values must match the pattern "%pattern"'] = '&lt;%tag %attribute="%value" 上で無効な�?です。有効な値はパターン "%pattern" にマッチしていなければなりません�?;
$dictionary['Showing XHTML code'] = 'XHTML コードを表示していま�?;
$dictionary['You have repeated the id %id %count times on your xhtml code. Duplicated Ids found on %tags'] = ' xhtml コード上�?id %id �?%count 回繰り返しています�?重複した Id �?%tags 上で見つかりました�?';
$dictionary['Tag %tag requires %attributes to be defined'] = 'タグ %tag �?%attributes を定義する必要があります';
$dictionary['Tag <%tag> is not allowed within tag <%previous<>'] = 'タグ <%tag> はタ�?<%previous<> 内では許可されていません';
$dictionary['Tag %tag is not allowed within tag %previous'] = 'タグ %tag はタ�?%previous 内では許可されていません';
$dictionary['Missing required attribute %attribute on &lt;%tag&gt;'] = '&lt;%tag&gt; タグ上の必須属�? %attribute が見つかりません';
$dictionary['Repeating id %id'] = 'id %id を繰り返していま�?;
$dictionary['duplicate attribute'] = '重複した属�?';
$dictionary['XHTML is not well-formed.'] = 'XHTML �?well-formed ではありませ�?;
$dictionary['Illegal tag: <code>%tag</code>'] = '不当なタ�? <code>%tag</code>';
$dictionary['first page'] = '�?��のページ';
$dictionary['previous page'] = '前のペー�?;
$dictionary['next page'] = '次のペー�?;
$dictionary['last page'] = '�?��のページ';
$dictionary['page'] = 'ペー�?;
$dictionary['show all'] = 'すべてを表示';
$dictionary['previous'] = '�?;
$dictionary['next'] = '�?;
$dictionary['Showing page %page of %number_of_pages'] = '%page / %number_of_pages ページを表示していま�?;
$dictionary['first'] = '�?��';
$dictionary['last'] = '�?��';
$dictionary['You can\'t use ${ within templates'] = 'テンプレート内で ${ を使用できません';
$dictionary['You must set the settings for current locale first by calling Ak::locale(null, $locale, $settings)'] = 'まず�?Ak::locale(null, $locale, $settings) をコールするして現在のロケール設定をしなければなりません�?;
$dictionary['Rails'] = 'Rails';
$dictionary['Could not load %converter_class_name converter class'] = '%converter_class_name コンバータクラスをロードできませんでした';
$dictionary['Could not locate %from to %to converter on %file_name'] = '%file_name 上で %from から %to コンバータを設置できませんでした';
$dictionary['Xdoc2Text is a windows only application. Please use wvWare instead'] = 'Xdoc2Text �?windows 専用アプリケーションです。代わり�?wvWare を使用してください�?';
$dictionary['Could not find xdoc2txt.exe on %path. Please download it from http://www31.ocn.ne.jp/~h_ishida/xdoc2txt.html'] = 'xdoc2txt.exe �?%path 上に見つかりませんでした。http://www31.ocn.ne.jp/~h_ishida/xdoc2txt.html から�?��ンロードしてください�?;
$dictionary['Loading...'] = '読み込み�?..';
$dictionary['%arg option required'] = '%arg オプションが必須です';
$dictionary['Cannot read file %path'] = 'ファイル %path を読み込めません';
$dictionary['Table %table_name already exists on the database'] = 'テーブル %table_name は既にデータベース上にありま�?;
$dictionary['You must supply a valid UNIX timestamp. You can get the timestamp by calling Ak::getTimestamp("2006-09-27 20:45:57")'] = '有効�?UNIX タイムスタンプを与えなければなりません�?タイムスタンプは Ak::getTimestamp("2006-09-27 20:45:57") をコールすることで取得できます�?';
$dictionary['Sorry but you can\'t view configuration files.'] = '申し訳ありませんが�?設定ファイルを見ることができませ�?;
$dictionary['Opsss! File highlighting is only available on development mode.'] = 'ファイルのハイライト表示は開発モード時のみ有効で�?;
$dictionary['%file_name is not available for showing its source code'] = '%file_name はソースコードを表示する際に有効ではありませ�?;
$dictionary['Your current PHP settings do not have support for %database_type databases.'] = '現在�?PHP 設定では %database_type データベースがサポートされていません';

$dictionary['Could not connect to the ftp server'] = 'FTP サーバに接続できませ�?;
$dictionary['Could not change to the FTP base directory %directory'] = 'FTP ベースディレクト�?%directory に移動できません';
$dictionary['Could not change to the FTP directory %directory'] = 'FTP ディレクトリ %directory に移動できません';
$dictionary['Ooops! Could not fetch details for the table %table_name.'] = 'テーブル %table_name の詳細を取得できませ�?;

$dictionary['Upgrading'] = '更新�?;
$dictionary['Could not find the file /app/controllers/<i>%controller_file_name</i> for the controller %controller_class_name'] = 'コントローラ %controller_class_name のファイ�?/app/controllers/<i>%controller_file_name</i> が見つかりませんでし�?;

$dictionary['Please add force=true to the argument list in order to overwrite existing files.'] = '既に存在するファイルを上書きするために引数リスト�?force=true を追加してくださ�?;
$dictionary['Could not find a helper to handle the method "%method" you called in your view'] = 'ビューでコールされたメソッド "%method" を扱うヘルパーが見つかりませんでした';
$dictionary['Could not locate usage file for this generator'] = 'このジェネレータの使用方法を記述したファイル（usage file）が配置されていませ�?;
$dictionary['You must supply a valid generator as the first command.

   Available generator are:'] = '第１引数として有効なジェネレータを指定する必要があります�?
   有効なジェネレー�?';

?>
