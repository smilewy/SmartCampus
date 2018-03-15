/**
 * Created by wqq on 2017/6/20.
 */
import Vuex from 'vuex'
import Vue from 'vue'
import * as mutations from './mutations'
import * as actions from './actions'
import * as getters from './getters'

Vue.use(Vuex);

const state={
  NavInfo:[],//导航数组
  /*排课管理排课方案具体信息——chart页面*/
  theArrangeClasses:'',
  pkListId:15,
  /*调查问卷——编辑及新建问卷处预览页面数据*/
  scanData:{},
  questionN:'',
  explainN:'',//问卷描述
  /*新生分班*/
  gradeId:'',//每个页面都需要的gradeId
  /*新生管理----------------------------*/
  /*新生管理默认信息*/
  // newStudentManHanlder:{},
  /*当前年级*/
/*  newStudentGradeMsg:{
    grade:'',
    gradeId:''
  },*/
  /*wy*/
  userInfo:{}
};
export default new Vuex.Store({
  state,
  mutations,
  actions,
  getters
});

