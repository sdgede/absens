import 'package:dartz/dartz.dart';
import '../../data/model/essential/failure.dart';

abstract class ILeaveRepository {
  Future<Either<Failure, List<dynamic>>> getLeaves();
  Future<Either<Failure, Map<String, dynamic>>> requestLeave(
      Map<String, dynamic> data);
  Future<Either<Failure, void>> approveLeave(int id);
  Future<Either<Failure, void>> rejectLeave(int id);
  Future<Either<Failure, Map<String, dynamic>>> getBalance();
  Future<Either<Failure, List<dynamic>>> getTypes();
}
