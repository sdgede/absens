import 'package:dartz/dartz.dart';
import '../../data/model/essential/failure.dart';
import '../../data/remote/leave_api.dart';
import '../interfaces/i_leave_repository.dart';

class LeaveRepositoryImpl implements ILeaveRepository {
  final LeaveApi leaveApi;
  LeaveRepositoryImpl({required this.leaveApi});

  @override
  Future<Either<Failure, List<dynamic>>> getLeaves() async {
    final r = await leaveApi.list();
    return r.fold(Left.new, (res) => Right(res.data ?? []));
  }

  @override
  Future<Either<Failure, Map<String, dynamic>>> requestLeave(
      Map<String, dynamic> data) async {
    final r = await leaveApi.request(data);
    return r.fold(Left.new, (res) => Right(res.data ?? {}));
  }

  @override
  Future<Either<Failure, void>> approveLeave(int id) async {
    final r = await leaveApi.approve(id);
    return r.fold(Left.new, (_) => const Right(null));
  }

  @override
  Future<Either<Failure, void>> rejectLeave(int id) async {
    final r = await leaveApi.reject(id);
    return r.fold(Left.new, (_) => const Right(null));
  }

  @override
  Future<Either<Failure, Map<String, dynamic>>> getBalance() async {
    final r = await leaveApi.balance();
    return r.fold(Left.new, (res) => Right(res.data ?? {}));
  }

  @override
  Future<Either<Failure, List<dynamic>>> getTypes() async {
    final r = await leaveApi.types();
    return r.fold(Left.new, (res) => Right(res.data ?? []));
  }
}
