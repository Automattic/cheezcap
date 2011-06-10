<?php
/**
 * CheezCap - Cheezburger Custom Administration Panel
 * (c) 2008 - 2011 Cheezburger Network (Pet Holdings, Inc.)
 * LOL: http://cheezburger.com
 * Source: http://github.com/cheezburger/cheezcap/
 * Authors: Kyall Barrows, Toby McKes, Stefan Rusek, Scott Porad
 * UnLOLs by Mo Jangda (batmoo@gmail.com)
 * License: GNU General Public License, version 2 (GPL), http://www.gnu.org/licenses/gpl-2.0.html
 */

class CheezCapGroup {
	var $name;
	var $id;
	var $options;

	function __construct( $_name, $_id, $_options ) {
		$this->name = $_name;
		$this->id = "cap_$_id";
		$this->options = $_options;
	}

	function write_html() {
		?>
		<table class="form-table" width="100%">
			<tr valign="top">
				<th scope="row">Option</th>
				<th scope="row">Value</th>
			</tr>
			<?php
			for ( $i=0; $i < count( $this->options ); $i++ ) {
				$this->options[$i]->write_html();
			}
			?>
		</table>
		<?php
	}
}

class CheezCapOption {
	var $name;
	var $desc;
	var $id;
	var $_key;
	var $std;

	function __construct( $_name, $_desc, $_id, $_std ) {
		$this->name = $_name;
		$this->desc = $_desc;
		$this->id = "cap_$_id";
		$this->_key = $_id;
		$this->std = $_std;
	}

	function write_html() {
	}

	function update( $ignored = '' ) {
		$value = isset( $_POST[$this->id] ) ? $_POST[$this->id] : '';
		$value = stripslashes_deep( $value );
		$this->save( $value );
	}

	function reset( $ignored = '' ) {
		$this->save( $this->std );
	}

	function import( $data ) {
		if ( array_key_exists( $this->id, $data->dict ) )
			$this->save( $data->dict[$this->id] );
	}

	function export( $data ) {
		$data->dict[$this->id] = get_option( $this->id );
	}

	function save( $value ) {
		update_option( $this->id, $value );
	}

	function get() {
		return get_option( $this->id );
	}
}

class CheezCapTextOption extends CheezCapOption {
	var $useTextArea;

	function __construct( $_name, $_desc, $_id, $_std = '', $_useTextArea = false ) {
		parent::__construct( $_name, $_desc, $_id, $_std );
		$this->useTextArea = $_useTextArea;
	}

	function write_html() {
		$stdText = $this->std;

		$stdTextOption = get_option( $this->id );
		if ( ! empty( $stdTextOption ) )
			$stdText = $stdTextOption;

		?>
		<tr valign="top">
			<th scope="row"><label for="<?php echo $this->id; ?>"><?php echo esc_html( $this->name . ':' ); ?></label></th>
			<?php
			$commentWidth = 2;
			if ( $this->useTextArea ) :
				$commentWidth = 1; ?>
				<td rowspan="2">
					<textarea style="width:100%;height:100%;" name="<?php echo esc_attr( $this->id ); ?>" id="<?php echo esc_attr( $this->id ); ?>"><?php echo esc_textarea( $stdText ); ?></textarea>
			<?php else : ?>
				<td>
					<input name="<?php echo esc_attr( $this->id ); ?>" id="<?php echo esc_attr( $this->id ); ?>" type="text" value="<?php echo esc_attr( $stdText ); ?>" size="40" />
			<?php endif; ?>
			</td>
		</tr>
		<tr valign="top">
			<td colspan="<?php echo absint( $commentWidth ); ?>">
				<label for="<?php echo $this->id; ?>">
					<small><?php echo esc_html( $this->desc ); ?></small>
				</label>
			</td>
		</tr>
		<tr valign="top">
			<td colspan="2"><hr /></td>
		</tr>
		<?php
	}

	function get() {
		$value = get_option( $this->id );
		if ( empty( $value ) )
			return $this->std;
		return $value;
	}
}

class CheezCapDropdownOption extends CheezCapOption {
	var $options;

	function __construct( $_name, $_desc, $_id, $_options, $_stdIndex = 0, $_options_labels = array() ) {
		parent::__construct( $_name, $_desc, $_id, $_stdIndex );
		$this->options = $_options;
		$this->options_labels = $_options_labels;
	}

	function update( $ignored = '' ) {
		$value = isset( $_POST[$this->id] ) ? $_POST[$this->id] : '';
		$value = stripslashes_deep( $value );
		if( ! in_array( $value, $this->options ) )
			$this->reset();
		else
			$this->save( $value );
	}

	function write_html() {
		?>
		<tr valign="top">
			<th scope="row"><label for="<?php echo $this->id; ?>"><?php echo esc_html( $this->name ); ?></label></th>
			<td>
				<select name="<?php echo esc_attr( $this->id ); ?>" id="<?php echo esc_attr( $this->id ); ?>">
				<?php $count = 0; ?>
				<?php foreach( $this->options as $option ) : ?>
					<?php $option_label = isset( $this->options_labels[$count] ) ? $this->options_labels[$count] : $option; ?>
					
					<option<?php selected( ( get_option( $this->id ) == $option || ( ! get_option( $this->id ) && $this->options[$this->std] == $option ) ) ) ?> value="<?php echo esc_attr( $option ); ?>"><?php echo esc_html( $option_label ); ?></option>
					
					<?php $count++; ?>
				<?php endforeach; ?>
				</select>
			</td>
		</tr>
		<tr valign="top">
			<td colspan=2>
				<label for="<?php echo $this->id; ?>"><small><?php echo esc_html( $this->desc ); ?></small></label><hr />
			</td>
		</tr>
		<?php
	}

	function get() {
		$value = get_option( $this->id, $this->std );
		if ( strtolower( $value ) == 'disabled' )
			return false;
		return $value;
	}
}

class CheezCapBooleanOption extends CheezCapDropdownOption {
	var $default;

	function __construct( $_name, $_desc, $_id, $_default = false ) {
		$this->default = $_default;
		parent::__construct( $_name, $_desc, $_id, array( 0, 1 ), $_default ? 0 : 1, array( 'Disabled', 'Enabled' ) );
	}

	function get() {
		$value = get_option( $this->id, $this->default );
		if ( is_bool( $value ) )
			return $value;
		switch ( strtolower( $value ) ) {
			case 'true':
			case 'enable':
			case 'enabled':
			case '1':
			case 1:
				return true;
			default:
				return false;
		}
	}
}

class CheezCapImportData {
	var $dict = array();
}
