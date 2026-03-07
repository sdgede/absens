import 'package:flutter/material.dart';

/// A labeled text field for general text input.
class InputTextLabel extends StatelessWidget {
  final String label;
  final String? hint;
  final TextEditingController? controller;
  final TextInputType? keyboardType;
  final TextInputAction? textInputAction;
  final IconData? prefixIcon;
  final String? Function(String?)? validator;
  final void Function(String)? onChanged;
  final bool readOnly;

  const InputTextLabel({
    super.key,
    required this.label,
    this.hint,
    this.controller,
    this.keyboardType,
    this.textInputAction,
    this.prefixIcon,
    this.validator,
    this.onChanged,
    this.readOnly = false,
  });

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          label,
          style: const TextStyle(
            fontSize: 13,
            fontWeight: FontWeight.w600,
            color: Color(0xFF1A1F36),
          ),
        ),
        const SizedBox(height: 6),
        TextFormField(
          controller: controller,
          keyboardType: keyboardType,
          textInputAction: textInputAction ?? TextInputAction.next,
          readOnly: readOnly,
          decoration: InputDecoration(
            hintText: hint,
            prefixIcon:
                prefixIcon != null ? Icon(prefixIcon, size: 20) : null,
          ),
          validator: validator,
          onChanged: onChanged,
        ),
      ],
    );
  }
}
