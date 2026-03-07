import 'package:dartz/dartz.dart';
import '../../data/model/essential/failure.dart';

abstract class IBranchRepository {
  Future<Either<Failure, List<dynamic>>> getBranches();
  Future<Either<Failure, Map<String, dynamic>>> getBranch(int id);
}
