/* global gutenblocksl10nAjax, ActiveXObject, JSON */
( function() {

	if ( typeof gutenblocksl10nAjax === 'undefined' ) {
		return;
	}

	var parent      = document.querySelector( '.gutenblocks-i18n-switcher' ).parentElement,
		loader      = document.createElement( 'IMG' ),
		ajaxRequest = function( data, response ) {
			var request, queryVars,
			    headers = {
			    	'X-Requested-With' : 'XMLHttpRequest',
			    	'Cache-Control'    : 'no-cache, must-revalidate, max-age=0',
			    	'Content-Type'     : 'application/x-www-form-urlencoded'
			    };

			if ( 'undefined' !== typeof XMLHttpRequest ) {
				request = new XMLHttpRequest();
			} else {
				request = new ActiveXObject( 'Microsoft.XMLHTTP' );
			}

			data = data || {};

			queryVars = Object.keys( data ).map( function( k ) {
				return encodeURIComponent( k ) + '=' + encodeURIComponent( data[k] );
			} ).join( '&' );

			request.onreadystatechange = function( event ) {
				if ( event.currentTarget && 4 === event.currentTarget.readyState ) {
					var r = JSON.parse( event.currentTarget.responseText ), status;

					if ( r.status ) {
						status = r.status;
					} else {
						status = event.currentTarget.status;
					}

					response && response( status, r );
				}
			};

			request.open( 'POST', gutenblocksl10nAjax.ajaxurl );

			for ( var h in headers ) {
				request.setRequestHeader( h, headers[h] );
			}

			request.send( queryVars );
		};

	parent.addEventListener( 'click', function( event ) {
		var i18nRequest, container = document.querySelector( '.wp-block-gutenblocks-i18n' ), currentHTML,
		    currentLI;

		if ( 'gutenblocks-i18n-switcher' === event.target.parentElement.parentElement.getAttribute( 'class' ) ) {
			event.preventDefault();

			if ( null !== document.querySelector( '#gutenblocks-ajax-error' ) ) {
				document.querySelector( '#gutenblocks-ajax-error' ).remove();
			}

			currentHTML = container.innerHTML;

			// No need to translate an already translated or being translated
			if ( -1 !== event.target.parentElement.getAttribute( 'class' ).indexOf( 'current-locale' ) || -1 !== container.innerHTML.indexOf( gutenblocksl10nAjax.loader ) ) {
				return;
			}

			event.target.parentElement.parentElement.childNodes.forEach( function( item ) {
				if ( 'LI' === item.nodeName && -1 !== item.getAttribute( 'class' ).indexOf( 'current-locale' ) ) {
					currentLI = item.getAttribute( 'class' ).replace( ' current-locale', '' );
					item.setAttribute( 'class', currentLI );
				}
			} );

			loader.setAttribute( 'src', gutenblocksl10nAjax.loader );
			loader.setAttribute( 'style', 'display: block; margin: 1em auto;' );

			container.innerHTML = '';
			container.appendChild( loader );

			i18nRequest = ajaxRequest( {
				'action': 'gutenblock_ajax_translate',
				'link'  : event.target.getAttribute( 'href' ),
				'nonce' : gutenblocksl10nAjax.nonce
			}, function( status, response ) {
				if ( 200 === status && response.post_content ) {
					event.target.parentElement.setAttribute( 'class', event.target.parentElement.getAttribute( 'class' ) + ' current-locale' );
					container.remove();
					parent.insertAdjacentHTML( 'beforeend', response.post_content );
				} else {
					document.querySelector( '.' + currentLI ).setAttribute( 'class', document.querySelector( '.' + currentLI ).getAttribute( 'class' ) + ' current-locale' );
					container.innerHTML = currentHTML;

					if ( response.message ) {
						container.insertAdjacentHTML( 'afterbegin', response.message );
						document.querySelector( '#gutenblocks-ajax-error' ).setAttribute( 'style', 'border-left: solid 3px #dc3232; padding: 0.5em;' );
					}
				}
			} );

			return;
		}

		return event;
	} );
} )();
