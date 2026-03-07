import 'dart:io';

import 'package:flutter/services.dart';

/// Provides a certificate-pinned [HttpClient].
///
/// Place your server DER-encoded cert at `assets/certificates/server.cer`.
/// Only TLS connections whose server certificate matches will be allowed.
class SecurityService {
  SecurityService._();

  /// Creates an [HttpClient] that pins to the bundled server certificate.
  static Future<HttpClient> createHttpClientWithPinning() async {
    // Load the cert from the asset bundle
    final certData =
        await rootBundle.load('assets/certificates/server.cer');
    final certBytes = certData.buffer.asUint8List();

    // Build a SecurityContext that trusts ONLY our certificate
    final ctx = SecurityContext(withTrustedRoots: false);
    ctx.setTrustedCertificatesBytes(certBytes);

    final client = HttpClient(context: ctx);

    // Custom badCertificateCallback: reject anything that doesn't match
    client.badCertificateCallback =
        (X509Certificate cert, String host, int port) {
      // Compare DER bytes of the pinned cert vs the server cert
      return cert.der == certBytes;
    };

    return client;
  }
}
