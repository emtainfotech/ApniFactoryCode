import 'package:apni_factory/provider/product_detail_provider.dart';
import 'package:apni_factory/res/colors.dart';
import 'package:flutter/material.dart';
import 'package:smooth_star_rating_null_safety/smooth_star_rating_null_safety.dart';

class StarWidget extends StatefulWidget {
  final ProductDetailProvider provider;
  const StarWidget({
    super.key,
    required this.provider,
  });

  @override
  State<StarWidget> createState() => _StarWidgetState();
}

class _StarWidgetState extends State<StarWidget> {
  @override
  Widget build(BuildContext context) {
    double height = MediaQuery.of(context).size.height;
    return Row(
      children: [
        SmoothStarRating(
            allowHalfRating: false,
            onRatingChanged: (v) {
              widget.provider.starRating(v);
            },
            starCount: 5,
            rating: widget.provider.rating,
            size: height / 30,
            filledIconData: Icons.star,
            halfFilledIconData: Icons.star_border,
            color: AppColors.primaryColor,
            borderColor: AppColors.primaryColor,
            spacing: 0.0)
      ],
    );
  }
}
