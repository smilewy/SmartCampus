/**
 * Created by wqq on 2017/6/20.
 */
const changeUser=(state,payload)=>{
  state.userInfo=payload.userInfo;
};
const initNavMutation=(state,payload)=>{
  state.NavInfo=payload.NavInfo;
};
/*修改当前排课方案信息*/
const changeArrangeClasses=(state,payload)=>{
  state.theArrangeClasses=payload.name;
  state.pkListId=payload.id
};
/*调查问卷*/
const scanDataInit=(state,payload)=>{
  state.scanData=payload;
};
/*新生管理*/
const newStudentManLoad=(state,payload)=>{
  state.newStudentManHanlder.content=payload.content;
  state.questionN=payload.questionN;
  state.explainN=payload.explainN;
  console.log(payload);
};
/*新生分班*/
const newStudentClassGrade=(state,payload)=>{
  state.gradeId=payload;
};

export {changeUser,initNavMutation,changeArrangeClasses,scanDataInit,newStudentManLoad,
  newStudentClassGrade,
}
