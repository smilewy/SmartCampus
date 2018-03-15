/**
 * Created by wqq on 2017/6/20.
 */
const CommitChangeUser=({commit},payload)=>{
  commit('changeUser',payload);
};
const initNavAction=({commit},payload)=>{
  commit('initNavMutation',payload);
};
/*调查问卷*/
const scanDataInit=({commit},payload)=>{
  commit('scanDataInit',payload);
};
/*新生管理*/
const newStudentManLoad=({commit},payload)=>{
  commit('newStudentManLoad',payload);
};
/*新生分班*/
const newStudentClassGrade=({commit},payload)=>{
  commit('newStudentClassGrade',payload);
};
export {CommitChangeUser,initNavAction,scanDataInit,newStudentManLoad,
  newStudentClassGrade
}

