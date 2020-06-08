function gf_google_clean_config(config) {
	var config = config.replace( 'const', '' );
	var config = config.replace( '=', '' );
	var config = config.replace( 'firebaseConfig', '' );
	var config = config.replace( ';', '' );
	return eval( '(' + config + ')' );
}

var firebaseConfig = firebase_data.firebaseConfig;
firebaseConfig     = gf_google_clean_config( firebaseConfig );

// Initialize Firebase
firebase.initializeApp( firebaseConfig );
var ui = new firebaseui.auth.AuthUI( firebase.auth() );
firebase.auth();
var uiConfig = {
	callbacks: {
		signInSuccessWithAuthResult: function (authResult, redirectUrl) {
			console.log( authResult );
			var user_token   = authResult.user.xa;
			var phone_number = authResult.user.phoneNumber;
			jQuery( '.gf_google_sms_otp_field' ).attr( 'type', 'text' ).val( phone_number );
			jQuery( '<input>' )
				.attr( 'type', 'hidden' )
				.attr( 'name', 'gf_firebase_user_token' )
				.val( user_token )
				.insertAfter( '.gf_google_sms_otp_field' );
			jQuery( '<input>' )
				.attr( 'type', 'hidden' )
				.attr( 'name', 'gf_firebase_api_key' )
				.val( firebaseConfig.apiKey )
				.insertAfter( '.gf_google_sms_otp_field' );
		},
		uiShown: function () {
			document.getElementsByClassName(
				'firebaseui-card-footer'
			)[0].style.display = 'none';
		},
	},
	signInFlow: 'popup',
	signInSuccessUrl: null,
	signInOptions: [
		{
			provider: firebase.auth.PhoneAuthProvider.PROVIDER_ID,
	},
	],
};

if (
	typeof firebase_data.firebase_countries !== 'undefined' &&
	firebase_data.firebase_countries.length > 0
) {
	uiConfig.signInOptions[0].defaultCountry       =
		firebase_data.firebase_countries[0];
	uiConfig.signInOptions[0].whitelistedCountries =
		firebase_data.firebase_countries;
}

ui.start( '.gf_google_sms_otp', uiConfig );
