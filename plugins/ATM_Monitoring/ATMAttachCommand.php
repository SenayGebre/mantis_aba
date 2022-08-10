<?php
# MantisBT - A PHP based bugtracking system

# MantisBT is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 2 of the License, or
# (at your option) any later version.
#
# MantisBT is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with MantisBT.  If not, see <http://www.gnu.org/licenses/>.

require_api( 'authentication_api.php' );
require_api( 'bug_api.php' );
require_api( 'constant_inc.php' );
require_api( 'config_api.php' );
require_api( 'helper_api.php' );
require_api( 'user_api.php' );

use Mantis\Exceptions\ClientException;

/**
 * A command that attaches a atm to an issue and attempts to create it
 * if not already defined.
 *
 * {
 *   "query": { "issue_id" => 1234 },
 *   "payload": {
 *     "atms": [
 *       {
 *          "id": 1
 *       },
 *       {
 *          "terminal_id": "atm2"
 *       },
 *       {
 *          "id": 3,
 *          "terminal_id": "atm3"
 *       }
 *     ]
 *   }
 * }
 */
class ATMAttachCommand extends Command {
	/**
	 * @var integer issue id
	 */
	private $issue_id;

	/**
	 * @var integer logged in user id
	 */
	private $user_id;

	/**
	 * @var array Array of atm terminal_ids to be added.
	 */
	private $atmsToCreate = array();

	/**
	 * @var array Array of atm ids to be attached.  This doesn't include atms to be created the attached.
	 */
	private $atmsToAttach = array();

	/**
	 * @param array $p_data The command data.
	 */
	function __construct( array $p_data ) {
		parent::__construct( $p_data );
	}

	/**
	 * Validate the data.
	 */
	function validate() {
		$this->issue_id = helper_parse_issue_id( $this->query( 'issue_id' ) );
		$this->user_id = auth_get_current_user_id();

		if( !access_has_bug_level( config_get( 'atm_attach_threshold' ), $this->issue_id, $this->user_id ) ) {
			throw new ClientException( 'Access denied to attach atms', ERROR_ACCESS_DENIED );
		}

		$t_atms = $this->payload( 'atms', array() );
		if( !is_array( $t_atms ) || empty( $t_atms ) ) {
			throw new ClientException( 'Invalid atms array', ERROR_INVALID_FIELD_VALUE, array( 'atms' ) );
		}

		$t_can_create = access_has_global_level( config_get( 'atm_create_threshold' ) );

		foreach( $t_atms as $t_atm ) {
			if( isset( $t_atm['id'] ) ) {
				atm_ensure_exists( $t_atm['id'] );
				$this->atmsToAttach[] = (int)$t_atm['id'];
			} else if( isset( $t_atm['terminal_id'] ) ) {
				$t_atm_row = atm_get_by_terminal_id( $t_atm['terminal_id'] );
				if( $t_atm_row === false ) {
					if( $t_can_create ) {
						if( !in_array( $t_atm['terminal_id'], $this->atmsToCreate ) ) {
							$this->atmsToCreate[] = $t_atm['terminal_id'];
						}
					} else {
						throw new ClientException(
							sprintf( "ATM '%s' not found.  Access denied to auto-create atm.", $t_atm['terminal_id'] ),
							ERROR_INVALID_FIELD_VALUE,
							array( 'atms' ) );
					}
				} else {
					$this->atmsToAttach[] = (int)$t_atm_row['id'];
				}
			} else {
				# invalid atm with no id or terminal_id.
				throw new ClientException( "Invalid atm with no id or terminal_id", ERROR_INVALID_FIELD_VALUE, array( 'atms' ) );
			}
		}
	}

	/**
	 * Process the command.
	 *
	 * @returns array Command response
	 */
	protected function process() {
		$t_attached_atms = array();

		# Attach atms that already exist
		foreach( $this->atmsToAttach as $t_atm_id ) {
			if( !atm_bug_is_attached( $t_atm_id, $this->issue_id ) ) {
				atm_bug_attach( $t_atm_id, $this->issue_id, $this->user_id );
				$t_attached_atms[] = atm_get( $t_atm_id );
			}
		}

		# Create new atms and then attach them
		foreach( $this->atmsToCreate as $t_atm_terminal_id ) {
			$t_atm_id = atm_create( $t_atm_terminal_id, $this->user_id );
			if( !atm_bug_is_attached( $t_atm_id, $this->issue_id ) ) {
				atm_bug_attach( $t_atm_id, $this->issue_id, $this->user_id );
				$t_attached_atms[] = atm_get( $t_atm_id );
			}
		}

		if( !empty( $t_attached_atms ) ) {
			event_signal( 'EVENT_ATM_ATTACHED', array( $this->issue_id, $t_attached_atms ) );
		}
	}
}

