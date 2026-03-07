import 'package:dartz/dartz.dart';
import '../../data/model/essential/failure.dart';
import '../../data/remote/branch_api.dart';
import '../interfaces/i_branch_repository.dart';

class BranchRepositoryImpl implements IBranchRepository {
  final BranchApi branchApi;
  BranchRepositoryImpl({required this.branchApi});

  @override
  Future<Either<Failure, List<dynamic>>> getBranches() async {
    final r = await branchApi.list();
    return r.fold(Left.new, (res) => Right(res.data ?? []));
  }

  @override
  Future<Either<Failure, Map<String, dynamic>>> getBranch(int id) async {
    final r = await branchApi.detail(id);
    return r.fold(Left.new, (res) => Right(res.data ?? {}));
  }
}
