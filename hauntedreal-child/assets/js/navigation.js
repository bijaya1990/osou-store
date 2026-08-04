/**
 * HauntedReal — the entire client-side JavaScript payload.
 *
 * Two disclosure widgets (menu, search) and an Escape handler. Everything
 * else in this theme is CSS. Loaded with defer, roughly 1KB.
 */
( function () {
	'use strict';

	/**
	 * Wire a button to the panel it controls.
	 *
	 * @param {HTMLElement} button  Trigger with aria-controls + aria-expanded.
	 * @param {Function}    onOpen  Called when the panel opens.
	 * @return {Object|null} Controller with open/close/isOpen.
	 */
	function disclosure( button, onOpen ) {
		if ( ! button ) {
			return null;
		}

		var panel = document.getElementById( button.getAttribute( 'aria-controls' ) );

		if ( ! panel ) {
			return null;
		}

		function setState( open ) {
			button.setAttribute( 'aria-expanded', open ? 'true' : 'false' );
			panel.classList.toggle( 'is-open', open );

			if ( open && typeof onOpen === 'function' ) {
				onOpen( panel );
			}
		}

		button.addEventListener( 'click', function () {
			setState( button.getAttribute( 'aria-expanded' ) !== 'true' );
		} );

		return {
			close: function () {
				setState( false );
			},
			isOpen: function () {
				return button.getAttribute( 'aria-expanded' ) === 'true';
			},
			button: button
		};
	}

	var nav = disclosure( document.querySelector( '.hr-navtoggle' ) );

	var search = disclosure( document.querySelector( '.hr-searchtoggle' ), function ( panel ) {
		// Opening a search box that isn't focused is just a layout change.
		var field = panel.querySelector( 'input[type="search"]' );

		if ( field ) {
			field.focus();
		}
	} );

	// The two panels share the masthead; only one at a time.
	[ [ nav, search ], [ search, nav ] ].forEach( function ( pair ) {
		if ( pair[ 0 ] && pair[ 1 ] ) {
			pair[ 0 ].button.addEventListener( 'click', function () {
				if ( pair[ 0 ].isOpen() ) {
					pair[ 1 ].close();
				}
			} );
		}
	} );

	document.addEventListener( 'keydown', function ( event ) {
		if ( event.key !== 'Escape' ) {
			return;
		}

		[ nav, search ].forEach( function ( widget ) {
			if ( widget && widget.isOpen() ) {
				widget.close();
				// Send focus back to the control that opened the panel.
				widget.button.focus();
			}
		} );
	} );

	/**
	 * Above 1024px the menu is a permanent bar, so any open mobile drawer
	 * state left behind by a rotation or resize should be discarded.
	 */
	var desktop = window.matchMedia( '(min-width: 1024px)' );

	function handleBreakpoint( query ) {
		if ( query.matches && nav && nav.isOpen() ) {
			nav.close();
		}
	}

	if ( typeof desktop.addEventListener === 'function' ) {
		desktop.addEventListener( 'change', handleBreakpoint );
	}
}() );
