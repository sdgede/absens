class ModelBranch {
  final String id;
  final String name;
  final double lat;
  final double lng;
  final double radiusMeter;
  final String? address;

  const ModelBranch({
    required this.id,
    required this.name,
    required this.lat,
    required this.lng,
    required this.radiusMeter,
    this.address,
  });

  factory ModelBranch.fromJson(Map<String, dynamic> json) {
    return ModelBranch(
      id: (json['id'] ?? '').toString(),
      name: json['name'] as String? ?? '',
      lat: (json['lat'] ?? json['latitude'] ?? 0).toDouble(),
      lng: (json['lng'] ?? json['longitude'] ?? 0).toDouble(),
      radiusMeter: (json['radius_meter'] ?? json['radius'] ?? 100).toDouble(),
      address: json['address'] as String?,
    );
  }

  Map<String, dynamic> toJson() => {
        'id': id,
        'name': name,
        'lat': lat,
        'lng': lng,
        'radius_meter': radiusMeter,
        'address': address,
      };
}
