/**
 * Created by wqq on 2017/6/20.
 */
/*import axios from 'axios'
 export const loginHTTP=params=>{return axios.get('http://192.168.10.15:8080/123qwe.php')}*/
/*封装发送请求方法*/
const domain = '/api/';
/*得到年级studentMessageGradeLoad，得到班级studentMessageClassLoad*/
/*首页*/
export const homeSignOut = params => {
  return $.ajax({url: domain + 'school/user/loginOut', data: params, dataType: 'json', type: 'post'}).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
export const scheduleHandle = params => {
  return $.ajax({url: domain + 'school/Home/schedule', data: params, dataType: 'json', type: 'post'}).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
export const homeContact = params => {
  return $.ajax({url: domain + 'school/Home/contacts', data: params, dataType: 'json', type: 'post'}).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
export const HomeChannel = params => {
  return $.ajax({
    url: domain + 'school/Home/getChannel',
    data: params,
    dataType: 'json',
    type: 'post'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
export const HomeNotice = params => {
  return $.ajax({url: domain + 'school/Home/lists', data: params, dataType: 'json', type: 'post'}).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};

/*基础设施---------*/
/*学生信息管理*/
export const studentMessageGradeLoad = params => {
  return $.ajax({
    url: domain + 'school/User/getGrade?type=getGradeList',
    data: params,
    dataType: 'json',
    type: 'post'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
export const studentMessageClassLoad = params => {
  return $.ajax({
    url: domain + 'school/User/getGrade?type=getClass',
    data: params,
    dataType: 'json',
    type: 'get'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
export const studentMessageTypeLoad = params => {
  return $.ajax({
    url: domain + 'school/User/getGrade?type=getSelectType',
    data: params,
    dataType: 'json',
    type: 'get'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
export const studentMessageSearchLoad = params => {
  return $.ajax({
    url: domain + 'school/user/userGl?type=studentList',
    data: params,
    dataType: 'json',
    type: 'get'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
export const studentMessageDelete = params => {
  return $.ajax({
    url: domain + 'school/user/userGl?type=deleteStudent',
    data: params,
    dataType: 'json',
    type: 'post'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
export const studentMessageUpdate = params => {
  return $.ajax({
    url: domain + 'school/user/userGl?type=studentPersonalUpdata',
    data: params,
    dataType: 'json',
    type: 'post'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
/*学生批量导入*/
export const studentImportUpload = params => {
  return $.ajax({
    url: domain + 'school/user/userGl?type=export&roleNameEn=xs',
    data: params,
    processData: false,
    contentType: false,
    dataType: 'json',
    type: 'post'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
export const studentImportLoad = params => {
  return $.ajax({
    url: domain + '/school/user/userGl?type=getstudentAllSort',
    data: params,
    dataType: 'json',
    type: 'get'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};

/*学生个人信息*/
export const studentManagerGetGrade = params => {
  return $.ajax({url: domain + 'school/user/getStudentList', dataType: 'json', type: 'get'}).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
export const studentManagerGetMsg = params => {
  return $.ajax({
    url: domain + 'school/user/userGl?type=studentPersonalData',
    data: params,
    dataType: 'json',
    type: 'get'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
/*学生统计*/
export const studentStatusticsGrade = params => {
  return $.ajax({
    url: domain + 'school/User/getGrade?type=getGradeList',
    dataType: 'json',
    type: 'get'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
export const studentStatusticsMsg = params => {
  return $.ajax({
    url: domain + 'school/user/userGl?type=studentStatistics',
    data: params,
    dataType: 'json',
    type: 'get'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
export const studentStatusticsFilter = params => {
  return $.ajax({
    url: domain + 'school/user/userGl?type=studentStatisticsValue',
    data: params,
    dataType: 'json',
    type: 'get'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
export const studentStatusticsExport = params => {
  return $.ajax({
    url: domain + 'school/user/userGl?type=studentStatisticsExport',
    data: params,
    processData: false,
    contentType: false,
    dataType: 'json',
    type: 'get'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
/*学生补录*/
export const studentRecordMsg = params => {
  return $.ajax({
    url: domain + 'school/user/userGl?type=addStudent',
    data: params,
    dataType: 'json',
    type: 'post'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
/*角色信息管理*/
export const roleInformationUser = params => {
  return $.ajax({
    url: domain + 'school/user/userPassword?type=getUserType',
    data: params,
    dataType: 'json',
    type: 'get'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
export const roleInformationLoad = params => {
  return $.ajax({
    url: domain + 'school/user/userPassword?type=getList',
    data: params,
    dataType: 'json',
    type: 'get'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
export const roleInformationUpdataPwd = params => {
  return $.ajax({
    url: domain + 'school/user/userPassword?type=updataPassword',
    data: params,
    dataType: 'json',
    type: 'get'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
export const roleInformationDisabled = params => {
  return $.ajax({
    url: domain + 'school/user/userPassword?type=stopUse',
    data: params,
    dataType: 'json',
    type: 'get'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};

/*排课管理---------*/
/*排课管理首页面*/
export const arrangeManageLoad = params => {
  return $.ajax({
    url: domain + 'school/Curriculum/pkPlan?type=pkList',
    dataType: 'json',
    type: 'get'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
export const arrangeManageGradeRange = params => {
  return $.ajax({
    url: domain + 'school/Curriculum/pkPlan?type=getPkRang',
    dataType: 'json',
    type: 'get'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
export const arrangeManageAdd = params => {
  return $.ajax({
    url: domain + 'school/Curriculum/pkPlan?type=pkCreate',
    data: params,
    dataType: 'json',
    type: 'post'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
export const arrangeManageDelete = params => {
  return $.ajax({
    url: domain + 'school/Curriculum/deletePkList?type=pkDelete',
    data: params,
    dataType: 'json',
    type: 'post'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};

/*上课时间设置*/
export const courseSetting = params => {
  return $.ajax({
    url: domain + 'school/Curriculum/createPkPlan?type=timeSet',
    data: params,
    dataType: 'json',
    type: 'post'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
export const courseSettingLoad = params => {
  return $.ajax({
    url: domain + 'school/Curriculum/createPkPlan?type=pkTimeList',
    data: params,
    dataType: 'json',
    type: 'get'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};

/*教学计划*/
export const teachingPlanLoad = params => {
  return $.ajax({
    url: domain + 'school/curriculum/jxPlan?type=jsPlanList',
    data: params,
    dataType: 'json',
    type: 'get'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
export const teachingPlanCreate = params => {
  return $.ajax({
    url: domain + 'school/curriculum/jxPlan?type=createJxPlan',
    data: params,
    dataType: 'json',
    type: 'post'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};

/*班级上课限制表*/
export const classesTimeSettingTable = params => {
  return $.ajax({
    url: domain + 'school/Curriculum/limitCreateList?type=classSkTimeLimitList',
    data: params,
    dataType: 'json',
    type: 'get'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
export const classesTimeSettingGrade = params => {
  return $.ajax({
    url: domain + 'school/Curriculum/getGradeAndClass?type=getList',
    data: params,
    dataType: 'json',
    type: 'get'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
export const classesTimeSettingSaved = params => {
  return $.ajax({
    url: domain + 'school/Curriculum/limitCreateList?type=classSkTimeLimit',
    data: params,
    dataType: 'json',
    type: 'post'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};

/*课程上课时间限制*/
export const courseTimeSettingCourseLoad = params => {
  return $.ajax({
    url: domain + 'school/Curriculum/getSubjectList?type=pkList',
    dataType: 'json',
    type: 'get'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
export const courseTimeSettingTableLoad = params => {
  return $.ajax({
    url: domain + 'school/Curriculum/limitCreateList?type=subjectSkTimeList',
    data: params,
    dataType: 'json',
    type: 'get'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
export const courseTimeSettingSaved = params => {
  return $.ajax({
    url: domain + 'school/Curriculum/limitCreateList?type=subjectSkTimeLimit',
    data: params,
    dataType: 'json',
    type: 'post'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};

/*教师上课时间限制*/
export const teacherTimeSettingTeacherLoad = params => {
  return $.ajax({
    url: domain + 'school/Curriculum/getTeacherList?type=pkList',
    data: params,
    dataType: 'json',
    type: 'get'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
export const teacherTimeSettingTabelLoad = params => {
  return $.ajax({
    url: domain + 'school/Curriculum/limitCreateList?type=getTeacherListLimit',
    data: params,
    dataType: 'json',
    type: 'get'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
export const teacherTimeSettingSaved = params => {
  return $.ajax({
    url: domain + 'school/Curriculum/limitCreateList?type=jsSkTimeLimit',
    data: params,
    dataType: 'json',
    type: 'post'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};

/*课程预先安排*/
export const coursePreviousTableLoad = params => {
  return $.ajax({
    url: domain + 'school/Curriculum/limitCreateList?type=getArrangementList',
    data: params,
    dataType: 'json',
    type: 'get'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
export const coursePreviousSaved = params => {
  return $.ajax({
    url: domain + 'school/Curriculum/limitCreateList?type=kcArrangement',
    data: params,
    dataType: 'json',
    type: 'post'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};

/*合班上课*/
export const workTogetherTeacherLoad = params => {
  return $.ajax({
    url: domain + 'school/curriculum/getSubjectTeacher?type=pkList',
    data: params,
    dataType: 'json',
    type: 'get'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
export const workTogetherSetting = params => {
  return $.ajax({
    url: domain + 'school/Curriculum/limitCreateList?type=createHbKcSet',
    data: params,
    dataType: 'json',
    type: 'post'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
export const workTogetherGet = params => {
  return $.ajax({
    url: domain + 'school/Curriculum/limitCreateList?type=getHbKcSet',
    data: params,
    dataType: 'json',
    type: 'get'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
/*拆班*/
export const workSelfSet = params => {
  return $.ajax({
    url: domain + 'school/Curriculum/limitCreateList?type=delHb',
    data: params,
    dataType: 'json',
    type: 'post'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};

/*得到自动排课*/
export const AutomaticScheduceGet = params => {
  return $.ajax({
    url: domain + 'school/Curriculum/zdPk?type=getZdPkList',
    data: params,
    dataType: 'json',
    type: 'get'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
export const AutomaticScheduceSave = params => {
  return $.ajax({
    url: domain + 'school/Curriculum/startPktoGo',
    data: params,
    dataType: 'json',
    type: 'get'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
/*手动调整*/
export const ManualScheduceGetTeacher = params => {
  return $.ajax({
    url: domain + 'school/curriculum/mD?type=getTeacherList',
    data: params,
    dataType: 'json',
    type: 'get'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
export const ManualScheduceGetClass = params => {
  return $.ajax({
    url: domain + 'school/curriculum/mD?type=getClass',
    data: params,
    dataType: 'json',
    type: 'get'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
export const ManualScheduceTeacherCourse = params => {
  return $.ajax({
    url: domain + 'school/curriculum/mD?type=getTeacherTable',
    data: params,
    dataType: 'json',
    type: 'get'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
export const ManualScheduceClassCourse = params => {
  return $.ajax({
    url: domain + 'school/curriculum/mD?type=getClassTable',
    data: params,
    dataType: 'json',
    type: 'get'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
export const ManualScheduceChangeCourse = params => {
  return $.ajax({
    url: domain + 'school/curriculum/mD?type=adjustment',
    data: params,
    dataType: 'json',
    type: 'post'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
/*发布课程*/
export const PublishCourseGetLoad = params => {
  return $.ajax({
    url: domain + 'school/curriculum/release?type=getYear',
    data: params,
    dataType: 'json',
    type: 'get'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
export const PublishCourseSave = params => {
  return $.ajax({
    url: domain + 'school/curriculum/release?type=release',
    data: params,
    dataType: 'json',
    type: 'get'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
/*课程表*/
/*班级课表*/
export const classScheduleGetClass = params => {
  return $.ajax({
    url: domain + 'school/curriculum/table?type=getGradeAndClass',
    data: params,
    dataType: 'json',
    type: 'get'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
export const classScheduleGetCourse = params => {
  return $.ajax({
    url: domain + 'school/curriculum/table?type=getClassTable',
    data: params,
    dataType: 'json',
    type: 'get'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
/*教师课表*/
export const teacherScheduleGetTeacher = params => {
  return $.ajax({
    url: domain + 'school/curriculum/mD?type=getTeacherList',
    data: params,
    dataType: 'json',
    type: 'get'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
export const teacherScheduleGetCourse = params => {
  return $.ajax({
    url: domain + 'school/curriculum/table?type=getTeacherTable',
    data: params,
    dataType: 'json',
    type: 'get'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
/*总课表*/
export const totalScheduleGetCourse = params => {
  return $.ajax({
    url: domain + 'school/curriculum/table?type=getAllClassTable',
    data: params,
    dataType: 'json',
    type: 'get'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};

/*新生信息*/
/*common*/
/*得到年级、得到某个学生信息*/
export const newStudentGetGrade = params => {
  return $.ajax({
    url: domain + 'school/StudentIni/common',
    data: params,
    dataType: 'json',
    type: 'post'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
/*新生管理页面*/
export const newStudentManagement = params => {
  return $.ajax({
    url: domain + 'school/StudentIni/newManage',
    data: params,
    dataType: 'json',
    type: 'post'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
export const newStudentManagementUpload = params => {
  return $.ajax({
    url: domain + 'school/StudentIni/newManage',
    data: params,
    processData: false,
    contentType: false,
    dataType: 'json',
    type: 'post'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
/*签约生管理*/
export const SignUpStudentManagementLoad = params => {
  return $.ajax({
    url: domain + 'school/StudentIni/signManage',
    data: params,
    dataType: 'json',
    type: 'post'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
/*新生分班*/
export const newStudentClassLoad = params => {
  return $.ajax({
    url: domain + 'school/StudentIni/allProcess',
    data: params,
    dataType: 'json',
    type: 'post'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
/*新生分班名单*/
export const newStudentClassNameLoad = params => {
  return $.ajax({
    url: domain + 'school/StudentIni/stuLists',
    data: params,
    dataType: 'json',
    type: 'post'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
/*创建班级*/
export const createdClassLoad = params => {
  return $.ajax({
    url: domain + 'school/StudentIni/createClass',
    data: params,
    dataType: 'json',
    type: 'post'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
/*分班成绩合成设置*/
export const organizeResultsSet = params => {
  return $.ajax({
    url: domain + 'school/StudentIni/scoreSetting',
    data: params,
    dataType: 'json',
    type: 'post'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
export const organizeResultsImportScore = params => {
  return $.ajax({
    url: domain + 'school/StudentIni/scoreManage',
    data: params,
    dataType: 'json',
    type: 'post'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
export const organizeResultsImportUpload = params => {
  return $.ajax({
    url: domain + 'school/StudentIni/scoreManage',
    processData: false,
    contentType: false,
    data: params,
    async: true,
    dataType: 'json',
    type: 'post'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
/*分班合成成绩情况*/
export const classResultTScore = params => {
  return $.ajax({
    url: domain + 'school/StudentIni/scoreInfo',
    data: params,
    dataType: 'json',
    type: 'post'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
/*指定学生到班*/
export const newStudentSpecialSet = params => {
  return $.ajax({
    url: domain + 'school/StudentIni/assignStu',
    data: params,
    dataType: 'json',
    type: 'post'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
/*快速分班*/
export const placementFastSet = params => {
  return $.ajax({
    url: domain + 'school/StudentIni/quickBranch',
    data: params,
    dataType: 'json',
    type: 'post'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
/*手动调整*/
export const changeBySelfLoad = params => {
  return $.ajax({
    url: domain + 'school/StudentIni/manualAdjust',
    data: params,
    dataType: 'json',
    type: 'post'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
export const changeBySelfAssign = params => {
  return $.ajax({
    url: domain + 'school/StudentIni/assignStudent',
    data: params,
    dataType: 'json',
    type: 'post'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
export const changeBySelfEqual = params => {
  return $.ajax({
    url: domain + 'school/StudentIni/equalChange',
    data: params,
    dataType: 'json',
    type: 'post'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
/*学生补录*/
export const newStudentRecordSet = params => {
  return $.ajax({
    url: domain + 'school/StudentIni/addStu',
    data: params,
    dataType: 'json',
    type: 'post'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
/*打印报表*/
export const printMessageLoad = params => {
  return $.ajax({
    url: domain + 'school/StudentIni/printReport',
    data: params,
    dataType: 'json',
    type: 'post'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};

/*教师考评------*/
export const TeacherEvaluationCreate = params => {
  return $.ajax({
    url: domain + 'school/TeacherEvaluate/createEvaluate',
    data: params,
    dataType: 'json',
    type: 'post'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};


/*学生成绩补充*/
/*两次对比*/
/*common*/
export const compareComGetGrade = params => {
  return $.ajax({
    url: domain + 'school/Achievement/contrast/type/findgrade',
    dataType: 'json',
    type: 'post'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
export const compareComGetClass = params => {
  return $.ajax({
    url: domain + 'school/Achievement/contrast/type/branch',
    data: params,
    dataType: 'json',
    type: 'post'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
/*成绩对比的获取考试*/
export const compareExamComGetExam = params => {
  return $.ajax({
    url: domain + 'school/Achievement/contrast/type/exam',
    data: params,
    dataType: 'json',
    type: 'post'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
/*其他页面的对比获取考试接口*/
export const compareComGetExam = params => {
  return $.ajax({
    url: domain + 'school/Achievement/contrast/type/findexam',
    data: params,
    dataType: 'json',
    type: 'post'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
/*成绩对比*/
export const compareScoreLoad = params => {
  return $.ajax({
    url: domain + 'school/Achievement/contrast/type/result',
    data: params,
    dataType: 'json',
    type: 'post'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
/*名次对比*/
export const compareRankLoad = params => {
  return $.ajax({
    url: domain + 'school/Achievement/contrast/type/rank',
    data: params,
    dataType: 'json',
    type: 'post'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
/*均分对比*/
export const compareAvgLoad = params => {
  return $.ajax({
    url: domain + 'school/Achievement/contrast/type/avg',
    data: params,
    dataType: 'json',
    type: 'post'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
/*模拟上线*/
/*common*/
export const simulationLineGetGrade = params => {
  return $.ajax({
    url: domain + 'school/Achievement/score/type/findgrade',
    dataType: 'json',
    type: 'post'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
export const simulationLineGetExam = params => {
  return $.ajax({
    url: domain + 'school/Achievement/score/type/findexam',
    data: params,
    dataType: 'json',
    type: 'post'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
/*总分上线*/
export const totalScoreLineLoad = params => {
  return $.ajax({
    url: domain + 'school/Achievement/score/type/totalscore',
    data: params,
    dataType: 'json',
    type: 'post'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
/*单科上线*/
export const subjectLineLoad = params => {
  return $.ajax({
    url: domain + 'school/Achievement/score/type/singlescore',
    data: params,
    dataType: 'json',
    type: 'post'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
/*上线对比---等待*/
export const compareLineLoad = params => {
  return $.ajax({
    url: domain + 'school/Achievement/score/type/onlineContrast',
    data: params,
    dataType: 'json',
    type: 'post'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
/*其他-------------*/
/*资产分类*/
export const assetClassifyLoad = params => {
  return $.ajax({
    url: domain + 'school/assets/assetsType?type=getAssetsType',
    data: params,
    dataType: 'json',
    type: 'get'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
export const assetClassifyDelete = params => {
  return $.ajax({
    url: domain + 'school/assets/assetsType?type=delete',
    data: params,
    dataType: 'json',
    type: 'post'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
export const assetClassifyCreate = params => {
  return $.ajax({
    url: domain + 'school/assets/assetsType?type=create',
    data: params,
    dataType: 'json',
    type: 'post'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
export const assetClassifyChange = params => {
  return $.ajax({
    url: domain + 'school/assets/assetsType?type=updata',
    data: params,
    dataType: 'json',
    type: 'post'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
export const assetClassifyUsed = params => {
  return $.ajax({
    url: domain + 'school/assets/assetsType?type=userType',
    data: params,
    dataType: 'json',
    type: 'post'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};

/*审批设置*/
/*approvalSettingType选择资产分类*/
export const approvalSettingType = params => {
  return $.ajax({
    url: domain + 'school/assets/approverSet?type=getAssetsType',
    data: params,
    dataType: 'json',
    type: 'get'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
export const approvalSettingPersonChoose = params => {
  return $.ajax({
    url: domain + 'school/assets/approverSet?type=getApproverUser',
    data: params,
    dataType: 'json',
    type: 'get'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
export const approvalSettingSave = params => {
  return $.ajax({
    url: domain + 'school/assets/approverSet?type=createApproverUser',
    data: params,
    dataType: 'json',
    type: 'post'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
export const approvalSettingGetPerson = params => {
  return $.ajax({
    url: domain + 'school/assets/approverSet?type=getApproverUserList',
    data: params,
    dataType: 'json',
    type: 'get'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};

/*批量导入*/
export const assetImportLoad = params => {
  return $.ajax({
    url: domain + 'school/assets/batchImport?type=getAssetsList',
    data: params,
    dataType: 'json',
    type: 'get'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
export const assetImportSearch = params => {
  return $.ajax({
    url: domain + 'school/assets/batchImport?type=getAssetsListValue',
    data: params,
    dataType: 'json',
    type: 'get'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
export const assetImportSort = params => {
  return $.ajax({
    url: domain + 'school/assets/batchImport?type=getAssetsListSort',
    data: params,
    dataType: 'json',
    type: 'get'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
export const assetImportUpload = params => {
  return $.ajax({
    url: domain + 'school/Assets/batchImport?type=submit',
    data: params,
    dataType: 'json',
    type: 'post'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
export const assetImportScan = params => {
  return $.ajax({
    url: domain + 'school/Assets/batchImport?type=import',
    processData: false,
    contentType: false,
    data: params,
    dataType: 'json',
    type: 'post'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};

/*资产入库*/
export const newStorageGetAsset = params => {
  return $.ajax({
    url: domain + 'school/Assets/assetStoreroom?type=getAssetsType',
    data: params,
    dataType: 'json',
    type: 'get'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
export const newStorageSave = params => {
  return $.ajax({
    url: domain + 'school/Assets/assetStoreroom?type=create',
    data: params,
    dataType: 'json',
    type: 'post'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
export const storageRecordLoad = params => {
  return $.ajax({
    url: domain + 'school/Assets/assetStoreroom?type=getAssetsList',
    data: params,
    dataType: 'json',
    type: 'get'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
export const storageRecordSearch = params => {
  return $.ajax({
    url: domain + 'school/Assets/assetStoreroom?type=getAssetsListValue',
    data: params,
    dataType: 'json',
    type: 'get'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
export const storageRecordSort = params => {
  return $.ajax({
    url: domain + 'school/Assets/assetStoreroom?type=getAssetsListSort',
    data: params,
    dataType: 'json',
    type: 'get'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
export const storageRecordDelete = params => {
  return $.ajax({
    url: domain + 'school/Assets/assetStoreroom?type=delete',
    data: params,
    dataType: 'json',
    type: 'post'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
export const storageRecordUpdate = params => {
  return $.ajax({
    url: domain + 'school/Assets/assetStoreroom?type=updata',
    data: params,
    dataType: 'json',
    type: 'post'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};

/*资产出库---*/
/*新增出库*/
export const newOutGetAsset = params => {
  return $.ajax({
    url: domain + 'school/Assets/assetOut?type=getAssetsType',
    data: params,
    dataType: 'json',
    type: 'get'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
export const newOutGetLoad = params => {
  return $.ajax({
    url: domain + 'school/Assets/assetOut?type=getAssetsList',
    data: params,
    dataType: 'json',
    type: 'get'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
export const newOutGetSearch = params => {
  return $.ajax({
    url: domain + 'school/Assets/assetOut?type=getAssetsListValue',
    data: params,
    dataType: 'json',
    type: 'get'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
export const newOutGetSort = params => {
  return $.ajax({
    url: domain + 'school/Assets/assetOut?type=getAssetsListSort',
    data: params,
    dataType: 'json',
    type: 'get'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
export const newOutGetOutStorage = params => {
  return $.ajax({
    url: domain + 'school/Assets/assetOut?type=createOut',
    data: params,
    dataType: 'json',
    type: 'post'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
export const newOutGetOutApprovel = params => {
  return $.ajax({
    url: domain + 'school/assets/assetOut?type=getUserList',
    data: params,
    dataType: 'json',
    type: 'get'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
/*出库记录*/
export const outRecordGetLoad = params => {
  return $.ajax({
    url: domain + 'school/Assets/assetOut?type=getOutRecord',
    data: params,
    dataType: 'json',
    type: 'get'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
export const outRecordGetSearch = params => {
  return $.ajax({
    url: domain + 'school/Assets/assetOut?type=getOutRecordValue',
    data: params,
    dataType: 'json',
    type: 'get'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
export const outRecordGetSort = params => {
  return $.ajax({
    url: domain + 'school/Assets/assetOut?type=getOutRecordSort',
    data: params,
    dataType: 'json',
    type: 'get'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
export const outRecordGetRevoke = params => {
  return $.ajax({
    url: domain + 'school/Assets/assetOut?type=revoke',
    data: params,
    dataType: 'json',
    type: 'post'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
export const outRecordGetDestory = params => {
  return $.ajax({
    url: domain + 'school/Assets/assetOut?type=destroy',
    data: params,
    dataType: 'json',
    type: 'post'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
export const outRecordGetChange = params => {
  return $.ajax({
    url: domain + 'school/assets/assetOut?type=updataOut',
    data: params,
    dataType: 'json',
    type: 'post'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};

/*资产借还---*/
/*新增领用*/
export const newReceiptGetAsset = params => {
  return $.ajax({
    url: domain + 'school/Assets/assetBrrowAndRetrun?type=getAssetsType',
    data: params,
    dataType: 'json',
    type: 'get'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
export const newReceiptGetLoad = params => {
  return $.ajax({
    url: domain + 'school/Assets/assetBrrowAndRetrun?type=getList',
    data: params,
    dataType: 'json',
    type: 'get'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
export const newReceiptGetSort = params => {
  return $.ajax({
    url: domain + 'school/Assets/assetBrrowAndRetrun?type=getListSort',
    data: params,
    dataType: 'json',
    type: 'get'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
export const newReceiptGetSearch = params => {
  return $.ajax({
    url: domain + 'school/Assets/assetBrrowAndRetrun?type=getListValue',
    data: params,
    dataType: 'json',
    type: 'get'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
export const newReceiptGetReceipt = params => {
  return $.ajax({
    url: domain + 'school/Assets/assetBrrowAndRetrun?type=createBrrow',
    data: params,
    dataType: 'json',
    type: 'post'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
export const newReceiptReceiptPerson = params => {
  return $.ajax({
    url: domain + 'school/assets/assetBrrowAndRetrun?type=getUser',
    data: params,
    dataType: 'json',
    type: 'get'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
/*领用记录*/
export const ReceiptRecordGetLoad = params => {
  return $.ajax({
    url: domain + 'school/Assets/assetBrrowAndRetrun?type=brrowRecord',
    data: params,
    dataType: 'json',
    type: 'get'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
export const ReceiptRecordGetSearch = params => {
  return $.ajax({
    url: domain + 'school/Assets/assetBrrowAndRetrun?type=brrowRecordValue',
    data: params,
    dataType: 'json',
    type: 'get'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
export const ReceiptRecordGetSort = params => {
  return $.ajax({
    url: domain + 'school/Assets/assetBrrowAndRetrun?type=brrowRecordSort',
    data: params,
    dataType: 'json',
    type: 'get'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
export const ReceiptRecordGetRevoke = params => {
  return $.ajax({
    url: domain + 'school/Assets/assetBrrowAndRetrun?type=revoke',
    data: params,
    dataType: 'json',
    type: 'get'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
export const ReceiptRecordGetReceipt = params => {
  return $.ajax({
    url: domain + 'school/Assets/assetBrrowAndRetrun?type=return',
    data: params,
    dataType: 'json',
    type: 'get'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
export const ReceiptRecordHandler = params => {
  return $.ajax({
    url: domain + 'school/Assets/assetBrrowAndRetrun?type=updataBrrow',
    data: params,
    dataType: 'json',
    type: 'post'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
/*归还记录*/
export const returnRecordGetLoad = params => {
  return $.ajax({
    url: domain + 'school/Assets/assetBrrowAndRetrun?type=returnRecord',
    data: params,
    dataType: 'json',
    type: 'get'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
export const returnRecordGetSearch = params => {
  return $.ajax({
    url: domain + 'school/Assets/assetBrrowAndRetrun?type=returnRecordSort',
    data: params,
    dataType: 'json',
    type: 'get'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
export const returnRecordGetSort = params => {
  return $.ajax({
    url: domain + 'school/Assets/assetBrrowAndRetrun?type=returnRecordValue',
    data: params,
    dataType: 'json',
    type: 'get'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
export const returnRecordHanlder = params => {
  return $.ajax({
    url: domain + 'school/Assets/assetBrrowAndRetrun?type=updataBack',
    data: params,
    dataType: 'json',
    type: 'post'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
/*资产审批*/
/*待审批*/
export const PendApprovalGetLoad = params => {
  return $.ajax({
    url: domain + 'school/assets/assetsApprove?type=getApporveList',
    data: params,
    dataType: 'json',
    type: 'get'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
export const PendApprovalGetSearch = params => {
  return $.ajax({
    url: domain + 'school/assets/assetsApprove?type=getApporveListValue',
    data: params,
    dataType: 'json',
    type: 'get'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
export const PendApprovalGetSort = params => {
  return $.ajax({
    url: domain + 'school/assets/assetsApprove?type=getApporveListSort',
    data: params,
    dataType: 'json',
    type: 'get'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
export const PendApprovalHandle = params => {
  return $.ajax({
    url: domain + 'school/assets/assetsApprove?type=approveHandle',
    data: params,
    dataType: 'json',
    type: 'get'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};

/*已审批*/
export const alreadyApprovalGetLoad = params => {
  return $.ajax({
    url: domain + 'school/assets/assetsApprove?type=getHaveApprove',
    data: params,
    dataType: 'json',
    type: 'get'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
export const alreadyApprovalGetSearch = params => {
  return $.ajax({
    url: domain + 'school/assets/assetsApprove?type=getHaveApproveValue',
    data: params,
    dataType: 'json',
    type: 'get'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
export const alreadyApprovalGetSort = params => {
  return $.ajax({
    url: domain + 'school/assets/assetsApprove?type=getHaveApproveSort',
    data: params,
    dataType: 'json',
    type: 'get'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
/*资产明细*/
export const assetDetailType = params => {
  return $.ajax({
    url: domain + 'school/assets/assetsList?type=getType',
    data: params,
    dataType: 'json',
    type: 'get'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
export const assetDetailLoad = params => {
  return $.ajax({
    url: domain + 'school/assets/assetsList?type=getAssetsList',
    data: params,
    dataType: 'json',
    type: 'get'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
export const assetDetailSearch = params => {
  return $.ajax({
    url: domain + 'school/assets/assetsList?type=getAssetsListValue',
    data: params,
    dataType: 'json',
    type: 'get'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
export const assetDetailSort = params => {
  return $.ajax({
    url: domain + 'school/assets/assetsList?type=getAssetsListSort',
    data: params,
    dataType: 'json',
    type: 'get'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
export const assetDetailChange = params => {
  return $.ajax({
    url: domain + 'school/assets/assetsList?type=update',
    data: params,
    dataType: 'json',
    type: 'post'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};

/*设备报修*/
/*基础设置*/
export const basicSettingLoad = params => {
  return $.ajax({
    url: domain + 'school/Eqrepair/basicsSet?type=getRepairType',
    data: params,
    dataType: 'json',
    type: 'get'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
export const basicSettingCreate = params => {
  return $.ajax({
    url: domain + 'school/Eqrepair/basicsSet?type=createRepairType',
    data: params,
    dataType: 'json',
    type: 'post'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
export const basicSettingDelete = params => {
  return $.ajax({
    url: domain + 'school/Eqrepair/basicsSet?type=deleteRepairType',
    data: params,
    dataType: 'json',
    type: 'post'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
export const basicSettingUpload = params => {
  return $.ajax({
    url: domain + 'school/Eqrepair/basicsSet?type=updataRepairType',
    data: params,
    dataType: 'json',
    type: 'post'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
export const basicSettingPerson = params => {
  return $.ajax({
    url: domain + 'school/Eqrepair/basicsSet?type=getZgList',
    data: params,
    dataType: 'json',
    type: 'get'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
export const basicSettingPersonDelete = params => {
  return $.ajax({
    url: domain + 'school/Eqrepair/basicsSet?type=deleteUser',
    data: params,
    dataType: 'json',
    type: 'post'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
/*我的保修单*/
export const repaymentLoad = params => {
  return $.ajax({
    url: domain + 'school/Eqrepair/myRepair?type=getRepairList',
    data: params,
    dataType: 'json',
    type: 'get'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
export const repaymentType = params => {
  return $.ajax({
    url: domain + 'school/Eqrepair/myRepair?type=getTypeList',
    data: params,
    dataType: 'json',
    type: 'get'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
export const repaymentDialogType = params => {
  return $.ajax({
    url: domain + 'school/Eqrepair/myRepair?type=getTypeListOne',
    data: params,
    dataType: 'json',
    type: 'get'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
export const repaymentCreate = params => {
  return $.ajax({
    url: domain + 'school/Eqrepair/myRepair?type=createOrUpdataRepairList',
    data: params,
    dataType: 'json',
    type: 'post'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};

/**
 * dubo
 * 2018-01-12
 * 新增保修单-删除上传图片
 * @param {*} params 
 */
export const repaymentPicDelete = params => {
  return $.ajax({
    url: domain + 'school/Eqrepair/myRepair?type=delImg',
    data: params,
    dataType: 'json',
    type: 'get'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
export const kcbInfogGget = params => {
    return $.ajax({
      url: domain + 'school/curriculum/getYe',
      data: params,
      dataType: 'json',
      type: 'get'
    }).then(data => {
      if (data.statu == 9) {
        sessionStorage.userInfo = '';
        location.reload();
      } else {
        return data;
      }
    })
  };
export const repaymentDelete = params => {
  return $.ajax({
    url: domain + 'school/Eqrepair/myRepair?type=deleteRepairList',
    data: params,
    dataType: 'json',
    type: 'post'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
export const repaymentUploadImg = params => {
  return $.ajax({
    url: domain + 'school/Eqrepair/myRepair?type=uploda',
    data: params,
    dataType: 'json',
    contentType: false,
    processData: false,
    type: 'post'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
export const repaymentAccept = params => {
  return $.ajax({
    url: domain + 'school/Eqrepair/myRepair?type=check',
    data: params,
    dataType: 'json',
    type: 'post'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
export const repaymentBack = params => {
  return $.ajax({
    url: domain + 'school/Eqrepair/myRepair?type=back',
    data: params,
    dataType: 'json',
    type: 'post'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
export const repaymentAgain = params => {
  return $.ajax({
    url: domain + 'school/Eqrepair/myRepair?type=restartApp',
    data: params,
    dataType: 'json',
    type: 'post'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
/*报修空间*/
export const repairSpaceType = params => {
  return $.ajax({
    url: domain + 'school/Eqrepair/repairList?type=getTypeList',
    data: params,
    dataType: 'json',
    type: 'get'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
export const repairSpaceLoad = params => {
  return $.ajax({
    url: domain + 'school/Eqrepair/repairList?type=getRepairList',
    data: params,
    dataType: 'json',
    type: 'get'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
/*维修任务*/
export const serviceTaskLoad = params => {
  return $.ajax({
    url: domain + 'school/Eqrepair/repairTask?type=getRepairTaskList',
    data: params,
    dataType: 'json',
    type: 'get'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
export const serviceTaskType = params => {
  return $.ajax({
    url: domain + 'school/Eqrepair/repairTask?type=getTypeList',
    data: params,
    dataType: 'json',
    type: 'get'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
export const serviceTaskOrder = params => {
  return $.ajax({
    url: domain + 'school/Eqrepair/repairTask?type=Orders',
    data: params,
    dataType: 'json',
    type: 'post'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
export const serviceTaskHanlder = params => {
  return $.ajax({
    url: domain + 'school/Eqrepair/repairTask?type=handle',
    data: params,
    dataType: 'json',
    type: 'post'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
export const serviceTaskUploadImg = params => {
  return $.ajax({
    url: domain + 'school/Eqrepair/repairTask?type=uploda',
    data: params,
    contentType: false,
    processData: false,
    dataType: 'json',
    type: 'post'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
export const serviceTaskHanlderMsg = params => {
  return $.ajax({
    url: domain + 'school/Eqrepair/repairTask?type=getRepairList',
    data: params,
    dataType: 'json',
    type: 'get'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
/*报修统计*/
export const repairStaticalLoad = params => {
  return $.ajax({
    url: domain + 'school/Eqrepair/repairStatistics?type=getList',
    data: params,
    dataType: 'json',
    type: 'get'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};

/*调查问卷*/
/*common*/
/*问卷记录*/
export const questionNaireRecordLoad = params => {
  return $.ajax({
    url: domain + 'school/Questionnaire/questionnaireRecord?type=getList',
    data: params,
    dataType: 'json',
    type: 'get'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
export const questionNaireRecordDelete = params => {
  return $.ajax({
    url: domain + 'school/Questionnaire/questionnaireRecord?type=delete',
    data: params,
    dataType: 'json',
    type: 'post'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
export const questionNaireRecordRelease = params => {
  return $.ajax({
    url: domain + 'school/Questionnaire/questionnaireRecord?type=startOrEnd',
    data: params,
    dataType: 'json',
    type: 'post'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
export const questionNaireRecordScan = params => {
  return $.ajax({
    url: domain + 'school/Questionnaire/questionnaireRecord?type=preview',
    data: params,
    dataType: 'json',
    type: 'get'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
export const questionNaireRecordCopy = params => {
  return $.ajax({
    url: domain + 'school/Questionnaire/questionnaireRecord?type=copy',
    data: params,
    dataType: 'json',
    type: 'post'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
/*问卷进度*/
export const questionNaireRecordProgress = params => {
  return $.ajax({
    url: domain + 'school/Questionnaire/questionnaireRecord?type=fillSpeed',
    data: params,
    dataType: 'json',
    type: 'get'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};

/*分享*/
export const questionNaireRecordShareUrl = params => {
  return $.ajax({
    url: domain + 'school/questionnaire/questionnaireRecord?type=share',
    data: params,
    dataType: 'json',
    type: 'get'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
/*编辑问卷*/
export const handlerQuestionNaireLoad = params => {
  return $.ajax({
    url: domain + 'school/Questionnaire/questionnaireRecord?type=getUpdata',
    data: params,
    dataType: 'json',
    type: 'get'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
export const handlerQuestionNaireSave = params => {
  return $.ajax({
    url: domain + 'school/Questionnaire/questionnaireRecord?type=updata',
    data: params,
    dataType: 'json',
    type: 'post'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
export const handlerQuestionNaireScanSave = params => {
  return $.ajax({
    url: domain + 'school/Questionnaire/questionnaireRecord?type=createUpdataPreview',
    data: params,
    dataType: 'json',
    type: 'get'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
export const handlerQuestionNaireScanLoad = params => {
  return $.ajax({
    url: domain + 'school/Questionnaire/questionnaireRecord?type=updataPreview',
    data: params,
    dataType: 'json',
    type: 'get'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
/*新建问卷*/
export const questionNairePerson = params => {
  return $.ajax({
    url: domain + 'school/Questionnaire/createQuestion?type=getUserList',
    data: params,
    dataType: 'json',
    type: 'get'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
export const newQuestionNaireSave = params => {
  return $.ajax({
    url: domain + 'school/Questionnaire/createQuestion?type=create',
    data: params,
    dataType: 'json',
    type: 'post'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
/*问卷统计----*/
/*统计图标*/
export const echartsStatisticQuestionN = params => {
  return $.ajax({
    url: domain + 'school/Questionnaire/Statistics?type=getQuList',
    data: params,
    dataType: 'json',
    type: 'get'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
export const echartsStatisticWD = params => {
  return $.ajax({
    url: domain + 'school/Questionnaire/Statistics?type=getWd',
    data: params,
    dataType: 'json',
    type: 'get'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
export const echartsStatisticNaire = params => {
  return $.ajax({
    url: domain + 'school/Questionnaire/Statistics?type=getQuestion',
    data: params,
    dataType: 'json',
    type: 'get'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
export const echartsStatisticLoad = params => {
  return $.ajax({
    url: domain + 'school/Questionnaire/Statistics?type=select',
    data: params,
    dataType: 'json',
    type: 'get'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};

/*回答详情*/
export const replyDetailLoad = params => {
  return $.ajax({
    url: domain + 'school/Questionnaire/Statistics?type=getXq',
    data: params,
    dataType: 'json',
    type: 'get'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
export const replyDetailScan = params => {
  return $.ajax({
    url: domain + 'school/Questionnaire/Statistics?type=fillTaskLook',
    data: params,
    dataType: 'json',
    type: 'get'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
/*填写任务*/
export const fillInTaskLoad = params => {
  return $.ajax({
    url: domain + 'school/Questionnaire/fillTask?type=getFillTaskList',
    data: params,
    dataType: 'json',
    type: 'get'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
export const fillInTaskFillLoad = params => {
  return $.ajax({
    url: domain + 'school/Questionnaire/fillTask?type=fillTask',
    data: params,
    dataType: 'json',
    type: 'get'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
export const fillInTaskFillSave = params => {
  return $.ajax({
    url: domain + 'school/Questionnaire/fillTask?type=fillTaskCreateOrUpdata',
    data: params,
    dataType: 'json',
    type: 'post'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
export const fillInTaskFillScan = params => {
  return $.ajax({
    url: domain + 'school/Questionnaire/fillTask?type=fillTaskLook',
    data: params,
    dataType: 'json',
    type: 'get'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
/*教师考评*/
/*新建*/
export const newEvaluationLoad = params => {
  return $.ajax({
    url: domain + 'school/TeacherEvaluate/createEvaluate',
    data: params,
    dataType: 'json',
    type: 'post'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
/*管理*/
export const evaluationManagementName = params => {
  return $.ajax({
    url: domain + 'school/TeacherEvaluate/getAllEvaluate',
    data: params,
    dataType: 'json',
    type: 'post'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
export const evaluationManagementLoad = params => {
  return $.ajax({
    url: domain + 'school/TeacherEvaluate/manageEvaluate',
    data: params,
    dataType: 'json',
    type: 'post'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
/*修改*/
export const changeEvaluationProgramLoad = params => {
  return $.ajax({
    url: domain + 'school/TeacherEvaluate/changeEvaluate',
    data: params,
    dataType: 'json',
    type: 'post'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
/*评委分组*/
export const judgesGroupGetJudge = params => {
  return $.ajax({
    url: domain + 'school/TeacherEvaluate/judgeLists',
    data: params,
    dataType: 'json',
    type: 'post'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
export const judgesGroupGetLoad = params => {
  return $.ajax({
    url: domain + 'school/TeacherEvaluate/judgeEvaluate',
    data: params,
    dataType: 'json',
    type: 'post'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
/*被考评分组*/
/*保存*/
export const judgeGroupSave = params => {
  return $.ajax({
    url: domain + 'school/TeacherEvaluate/groupOfEvaluate',
    data: params,
    dataType: 'json',
    type: 'post'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
/*得到数据*/
// export const judgeGroupLoad=params=>{return $.ajax({url:domain+'school/TeacherEvaluate/getJudgeGroup',data:params,dataType:'json',type:'post'}).then(data=>data)};
/*分配考评人员*/
export const addEvaluationPersonGroup = params => {
  return $.ajax({
    url: domain + 'school/TeacherEvaluate/getGroup',
    data: params,
    dataType: 'json',
    type: 'post'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
export const addEvaluationPersonIsDudges = params => {
  return $.ajax({
    url: domain + 'school/TeacherEvaluate/personnelLists',
    data: params,
    dataType: 'json',
    type: 'post'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
export const addEvaluationPersonIsTable = params => {
  return $.ajax({
    url: domain + 'school/TeacherEvaluate/addPersonnel',
    data: params,
    dataType: 'json',
    type: 'post'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
export const addEvaluationPersonDudges = params => {
  return $.ajax({
    url: domain + 'school/TeacherEvaluate/judgesLists',
    data: params,
    dataType: 'json',
    type: 'post'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
export const addEvaluationPersonTable = params => {
  return $.ajax({
    url: domain + 'school/TeacherEvaluate/addJudge',
    data: params,
    dataType: 'json',
    type: 'post'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
export const addEvaluationPersonLoad = params => {
  return $.ajax({
    url: domain + 'school/TeacherEvaluate/save',
    data: params,
    dataType: 'json',
    type: 'post'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
/*考评进度追踪*/
export const evaluationProgressLoad = params => {
  return $.ajax({
    url: domain + 'school/TeacherEvaluate/trackProgress',
    data: params,
    dataType: 'json',
    type: 'post'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
/*考评记录*/
export const evaluationRecordLoad = params => {
  return $.ajax({
    url: domain + 'school/TeacherEvaluate/evaluateLog',
    data: params,
    dataType: 'json',
    type: 'post'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
/*评委打分*/
export const judgesScoringParams = params => {
  return $.ajax({
    url: domain + 'school/TeacherEvaluate/getBelong',
    data: params,
    dataType: 'json',
    type: 'post'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
export const judgesScoringLoad = params => {
  return $.ajax({
    url: domain + 'school/TeacherEvaluate/judgeMark',
    data: params,
    dataType: 'json',
    type: 'post'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
/*统计分析*/
export const statisticalAnalysisParams = params => {
  return $.ajax({
    url: domain + 'school/TeacherEvaluate/getBelong',
    data: params,
    dataType: 'json',
    type: 'post'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
export const statisticalAnalysisLoad = params => {
  return $.ajax({
    url: domain + 'school/TeacherEvaluate/statisticsEva',
    data: params,
    dataType: 'json',
    type: 'post'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};

/*学生综合素养*/
/*方案管理*/
export const programManagementLoad = params => {
  return $.ajax({
    url: domain + 'school/Accomplishment/programme/type/programmeFind',
    data: params,
    dataType: 'json',
    type: 'post'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
export const programManagementRange = params => {
  return $.ajax({
    url: domain + 'school/Accomplishment/programme/type/findGrade',
    data: params,
    dataType: 'json',
    type: 'post'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
export const programManagementDirect = params => {
  return $.ajax({
    url: domain + 'school/Accomplishment/programme/type/findDirection',
    data: params,
    dataType: 'json',
    type: 'post'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
export const programManagementNew = params => {
  return $.ajax({
    url: domain + 'school/Accomplishment/programme/type/programmeAdd',
    data: params,
    dataType: 'json',
    type: 'post'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
export const programManagementDel = params => {
  return $.ajax({
    url: domain + 'school/Accomplishment/programme/type/programeDel',
    data: params,
    dataType: 'json',
    type: 'post'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
export const programManagementUpdate = params => {
  return $.ajax({
    url: domain + 'school/Accomplishment/programme/type/programmeUpdate',
    data: params,
    dataType: 'json',
    type: 'post'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
export const programManagementDetail = params => {
  return $.ajax({
    url: domain + 'school/Accomplishment/programme/type/xiangqing',
    data: params,
    dataType: 'json',
    type: 'post'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
/*素养考核方向*/
export const literacyAssessLoad = params => {
  return $.ajax({
    url: domain + 'school/Accomplishment/direction/type/findDirectionList',
    data: params,
    dataType: 'json',
    type: 'post'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
export const literacyAssessAdd = params => {
  return $.ajax({
    url: domain + 'school/Accomplishment/direction/type/insertDirection',
    data: params,
    dataType: 'json',
    type: 'post'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
export const literacyAssessDel = params => {
  return $.ajax({
    url: domain + 'school/Accomplishment/direction/type/delDirection',
    data: params,
    dataType: 'json',
    type: 'post'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
export const literacyAssessUpdateName = params => {
  return $.ajax({
    url: domain + 'school/Accomplishment/direction/type/saveDirection',
    data: params,
    dataType: 'json',
    type: 'post'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
export const literacyAssessUpdateLoad = params => {
  return $.ajax({
    url: domain + 'school/Accomplishment/direction/type/findProject',
    data: params,
    dataType: 'json',
    type: 'post'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
export const literacyAssessAddProject = params => {
  return $.ajax({
    url: domain + 'school/Accomplishment/direction/type/insertPproject',
    data: params,
    dataType: 'json',
    type: 'post'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
export const literacyAssessUpdateProject = params => {
  return $.ajax({
    url: domain + 'school/Accomplishment/direction/type/saveProject',
    data: params,
    dataType: 'json',
    type: 'post'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
export const literacyAssessUpdateDel = params => {
  return $.ajax({
    url: domain + 'school/Accomplishment/direction/type/projectDel',
    data: params,
    dataType: 'json',
    type: 'post'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
/*考核明细*/
export const AssessDetailName = params => {
  return $.ajax({
    url: domain + 'school/Accomplishment/detailed/type/findProgramme',
    data: params,
    dataType: 'json',
    type: 'post'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
export const AssessDetailStudent = params => {
  return $.ajax({
    url: domain + 'school/Accomplishment/detailed/type/findStudentList',
    data: params,
    dataType: 'json',
    type: 'post'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
export const AssessDetailLoad = params => {
  return $.ajax({
    url: domain + 'school/Accomplishment/detailed/type/mingxi',
    data: params,
    dataType: 'json',
    type: 'post'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
/*均分统计*/
export const AverageStatisticName = params => {
  return $.ajax({
    url: domain + 'school/Accomplishment/junfen/type/findProgramme',
    data: params,
    dataType: 'json',
    type: 'post'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
export const AverageStatisticLoad = params => {
  return $.ajax({
    url: domain + 'school/Accomplishment/junfen/type/junfenFind',
    data: params,
    dataType: 'json',
    type: 'post'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
/*学生素养评分*/
export const studentAssessScoreLoad = params => {
  return $.ajax({
    url: domain + 'school/Accomplishment/pingfen/type/pingfentongji',
    data: params,
    dataType: 'json',
    type: 'post'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
export const studentAssessScorePublish = params => {
  return $.ajax({
    url: domain + 'school/Accomplishment/pingfen/type/shangbao',
    data: params,
    dataType: 'json',
    type: 'post'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
// export const studentAssessScoreName=params=>{return $.ajax({url:domain+'school/Accomplishment/pingfen/type/findprogramme',data:params,dataType:'json',type:'post'}).then(data=>data)};
export const ChildAssessScoreStudent = params => {
  return $.ajax({
    url: domain + 'school/Accomplishment/pingfen/type/findStudentList',
    data: params,
    dataType: 'json',
    type: 'post'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
export const ChildAssessScoreLoad = params => {
  return $.ajax({
    url: domain + 'school/Accomplishment/pingfen/type/pingfenfind',
    data: params,
    dataType: 'json',
    type: 'post'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
export const ChildAssessScoreSave = params => {
  return $.ajax({
    url: domain + 'school/Accomplishment/pingfen/type/pingfen',
    data: params,
    dataType: 'json',
    type: 'post'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
/*综合素养成绩*/
export const integratedAssessScoreName = params => {
  return $.ajax({
    url: domain + 'school/Accomplishment/chengji/type/findprogramme',
    data: params,
    dataType: 'json',
    type: 'post'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};
export const integratedAssessScoreLoad = params => {
  return $.ajax({
    url: domain + 'school/Accomplishment/chengji/type/chengji',
    data: params,
    dataType: 'json',
    type: 'post'
  }).then(data => {
    if (data.statu == 9) {
      sessionStorage.userInfo = '';
      location.reload();
    } else {
      return data;
    }
  })
};





