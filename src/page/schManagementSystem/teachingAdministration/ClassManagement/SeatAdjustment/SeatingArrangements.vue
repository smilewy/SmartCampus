<template>
  <div class="SeatingArrangements">
    <h3>座位安排</h3>
    <el-row style="margin-top: 2rem;border-bottom: 1px solid #d2d2d2">
      <el-form :inline="true" class="demo-form-inline">
        <el-form-item label="年级：">
          <el-select style="width: 60%;" v-model="grade_id" placeholder="请选择年级" @change="getClassList(grade_id)">
            <el-option
              v-for="item in grade_list"
              :key="item.gradeId"
              :label="item.name"
              :value="item.gradeId">
            </el-option>
          </el-select>
        </el-form-item>
        <el-form-item label="班级：" style="margin-left: -5rem;">
          <el-select style="width: 60%;" v-model="class_id" placeholder="请选择班级" @change="getLayout(class_id)">
            <el-option
              v-for="item in class_list"
              :key="item.classid"
              :label="item.classname"
              :value="item.classid">
            </el-option>
          </el-select>
        </el-form-item>
      </el-form>
      <el-col :span="14">
        <el-col :span="21">
          <el-form :inline="true" class="demo-form-inline">
            <el-form-item label="排序依据：">
              <el-select style="width: 60%;" v-model="according_order" placeholder="请选择排序依据"
                         @change="changeAccordingOrder">
                <el-option
                  v-for="item in according_list"
                  :key="item.value"
                  :label="item.label"
                  :value="item.value">
                </el-option>
              </el-select>
            </el-form-item>
            <el-form-item v-if="according==='Score'" label="考试：" style="margin-left: -5rem;">
              <el-select style="width:60%;" v-model="exam_id" placeholder="请选择考试">
                <el-option
                  v-for="item in exam_list"
                  :key="item.examinationid"
                  :label="item.examination"
                  :value="item.examinationid">
                </el-option>
              </el-select>
            </el-form-item>
          </el-form>
        </el-col>
        <el-col :span="3" class="Seats-maintitle" style="border-right: 1px solid #d2d2d2">
          <div class="Seats-title" @click="createSeat()">
            <span>生成<br>座位</span>
          </div>
        </el-col>
        <el-col :span="21">
          <el-form :inline="true" class="demo-form-inline">
            <el-form-item label="性别规则：">
              <el-radio-group v-model="sex">
                <el-radio label="random">随机</el-radio>
                <el-radio label="together">男女同桌</el-radio>
                <el-radio label="part">男女不同桌</el-radio>
              </el-radio-group>
            </el-form-item>
          </el-form>
        </el-col>
      </el-col>
      <el-col :span="10">
        <el-col :span="20">
          <el-col :span="24">
            <el-col :span="12" class="Seat-right">
              全体下移&nbsp;&nbsp;
              <el-input-number size="small" v-model="allDown"></el-input-number>&nbsp;行
            </el-col>
            <el-col :span="12" style="text-align: right;">
              全体右移&nbsp;&nbsp;
              <el-input-number size="small" v-model="allRight"></el-input-number>&nbsp;行
            </el-col>
          </el-col>
          <el-col :span="24" style="margin-top:1.8rem">
            <span style="color:#ff6a6a;font-size: 1rem">*</span>&nbsp;&nbsp;&nbsp;
            <span style="color:#888888">负数即为相反方向</span>
          </el-col>
        </el-col>
        <el-col :span="3" :offset="1">
          <div class="Seats-title" @click="adjustSeat()">
            <span>快速<br>调整</span>
          </div>
        </el-col>
      </el-col>
    </el-row>
    <el-row>
      <el-col :span="24">
        <el-col :span="1" class="alertsBtn Seats-Btn">
          <el-button class="delete" title="打印" @click="operationData('print')">
            <img class="delete_unactive"
                 src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_dayin.png"
                 alt="">
            <img class="delete_active"
                 src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_dayin_highlight.png"
                 alt="">
          </el-button>
        </el-col>
        <el-col :span="21">
          <div class="Seats-top">讲台</div>
        </el-col>
        <el-col :span="2" class="alertsBtn Seats-Btn">
          <el-button type="primary" :loading="save===true" @click="arrangeSave()">保存</el-button>
        </el-col>
      </el-col>
      <el-col v-if="hasArrange" :span="24">
        <div style="display: none">
          <div class="Seat-wrap" id="Seat-wrap" style=" display: flex;flex-wrap: nowrap;align-items: center;overflow-x: auto;">
            <template v-for="(group,key) in seat_table">
              <div class="Seat-content" style="font-size: 0;min-width:5rem;margin:1rem auto 0 auto;display: inline-block;text-align: center;vertical-align: middle;flex-grow: 0;flex-shrink: 0;">
                <ul class="Seats-ul" v-for="(col,index) in group" style="font-size: 1rem;display: inline-block;margin-left: 1.5rem;vertical-align: top;">
                  <li class="Seats-li" style="width: 6.875rem;height: 2.5rem;border: 1px solid #d2d2d2;border-radius: .3rem;text-align: center;line-height: 2.5rem;margin-top: 1rem;cursor: pointer;list-style: none;" :class="seat.selected?'selected':''" v-for="seat in col" @click="SeatsliActive(seat)">
                    {{seat.name}}
                  </li>
                </ul>
              </div>
              <div class="Seats-black" style="text-align: center;font-size: 1.5rem;color: #999999;display: inline-block;margin: 0 3rem;flex-grow: 0;flex-shrink: 0;"
                   :style="{display:isLast(key,seat_table)?'none':'inline-block'}">过<br>道</div>
            </template>
          </div>
        </div>
        <div class="Seat-wrap">
          <template v-for="(group,key) in seat_table">
            <div class="Seat-content">
              <ul class="Seats-ul" v-for="(col,index) in group">
                <li class="Seats-li" :class="seat.selected?'selected':''" v-for="seat in col" @click="SeatsliActive(seat)">
                  {{seat.name}}
                </li>
              </ul>
            </div>
            <div class="Seats-black">过<br>道</div>
          </template>
        </div>
      </el-col>
      <el-col v-else>
        <div v-if="!hasLayout" style="padding: 50px 0;text-align: center;">
          该班没有座位布局
        </div>
        <div v-else style="padding: 50px 0;text-align: center;">
          该班没有座位安排
        </div>
      </el-col>
    </el-row>
  </div>
</template>
<script>
  import req from '@/assets/js/common'
  import formatdata from '@/assets/js/date'

  export default {
    data() {
      return {
        hasArrange:true,
        hasLayout:false,
        save:false,
        grade_list: [],
        class_list: [],
        according_list: [{
          label: '学号升序',
          value: 'serialNumber,asc'
        }, {
          label: '学号降序',
          value: 'serialNumber,desc'
        }, {
          label: '身高升序',
          value: 'height,asc'
        }, {
          label: '身高降序',
          value: 'height,desc'
        }, {
          label: '成绩升序',
          value: 'Score,asc'
        }, {
          label: '成绩降序',
          value: 'Score,desc'
        }],
        exam_list: [],
        student_list: [],
        group_columes: [],
        grade_id: '',
        class_id: '',
        one_line_num: 0,
        group_num: 0,
        one_colume_num: 0,
        according_order: '',
        according: '',
        order: '',
        exam_id: '',
        sex: 'random',
        allDown: 0,
        hasMoveDown: 0,
        allRight: 0,
        hasMoveRight: 0,
        layout_data: {},
        seat_table: {},
        selected_seat:null,
        editState:'init'
      }
    },
    created() {
      this.getGradeList();
    },
    methods: {
      isLast(key,obj){
        return key===Object.keys(obj)[Object.keys(obj).length-1];
      },
      operationData(type){
        if(!this.class_id){
           this.vmMsgWarning('暂无数据');
           return;
        }
        var printData = document.getElementById("Seat-wrap").innerHTML;
        var newWin = window.open("");//新打开一个空窗口
        newWin.document.write(printData);//将表格添加进新的窗口
        newWin.document.close();//在IE浏览器中使用必须添加这一句
        newWin.focus();//在IE浏览器中使用必须添加这一句

        newWin.print();//打印
        newWin.close();//关闭窗口
      },
      getGradeList() {
        req.ajaxSend('/school/ClassManage/common', 'post', {func: 'getClass', param: {kind:'exam'}}, (res) => {
          this.grade_list = res;
        })
      },
      getClassList(grade_id) {
        this.class_id='';
        this.exam_id='';
        let cur_option = this.grade_list.filter(val=>val.gradeId===grade_id)[0];
//        let cur_option = this.grade_list[grade_id];
        this.class_list = cur_option.class;
        this.exam_list = cur_option.exam;
      },
      getLayout(classId) {
        this.exam_id='';
        req.ajaxSend('/school/ClassManage/seatLayout', 'post', {view: 'layout', classId}, (res) => {
          let layout = res.layout;
          if(layout&&layout.group1&&layout.group1.colume1&&layout.group1.colume1.length){
            this.layout_data = res;
            this.hasLayout = true;
            req.ajaxSend('/school/ClassManage/seatLayout', 'post', {view: 'arrange', classId}, (res) => {
              let seats = res.arrange;
              if(seats&&seats.group1&&seats.group1.colume1&&seats.group1.colume1.length&&seats.group1.colume1[0].name){
                this.seat_table = seats;

                let layout = deepCopy(this.layout_data.layout),
                  rows = layout.group1.colume1.length,
                  group_num = Object.keys(layout).length,
                  group_columes = [],
                  cols = 0;
                for (let group in layout) {
                  let cur_cols = Object.keys(layout[group]).length;
                  group_columes.push(cur_cols);
                  cols += cur_cols;
                }

                this.group_columes = group_columes;
                this.one_line_num = cols;
                this.group_num = group_num;
                this.one_colume_num = rows;
                this.student_list = this.studentOrderFromSeat(this.seat_table);
                this.hasArrange = true;
              }else{
                this.hasArrange = false;
              }
            });
          }else{
            this.hasLayout = false;
          }
        });
      },
      changeAccordingOrder(val) {
        this.according = val.split(',')[0];
        this.order = val.split(',')[1];
      },
      students2seats(students){
        this.student_list = deepCopy(students);
        let layout = deepCopy(this.layout_data.layout),
          rows = layout.group1.colume1.length,
          group_num = Object.keys(layout).length,
          group_columes = [],
          cols = 0,
          toRight = true;
        for (let group in layout) {
          let cur_cols = Object.keys(layout[group]).length;
          group_columes.push(cur_cols);
          cols += cur_cols;
        }
        for(let i=0;i<rows*cols;i++){
          if(i>=students.length){
            this.student_list[i]={
              name:'',
              id:''
            }
          }
        }
        for (let group = 0; group <= group_num; group++) {
          for (let i = 0; i < group_columes[group]; i++) {
            layout[`group${group + 1}`][`colume${i + 1}`] = [];
          }
        }
        for (let i = 0; i < rows; i++) {
          toRight = !(i%2);
          if(toRight){
            for (let j = 0; j <= group_num; j++) {
              for (let k = 0; k < group_columes[j]; k++) {
                if(students.length){
                  layout[`group${j + 1}`][`colume${k + 1}`].push(students.shift());
                }else{
                  layout[`group${j + 1}`][`colume${k + 1}`].push({
                    name:"",
                    id:""
                  });
                }
              }
            }
          }else{
            let rowSeats = [];
            for (let j = 0; j < cols; j++){
              if(students.length){
                rowSeats.unshift(students.shift());
              }else{
                rowSeats.unshift({
                  name:"",
                  id:""
                });
              }
            }
            for (let j = 0; j <= group_num; j++) {
              for (let k = 0; k < group_columes[j]; k++) {
                layout[`group${j + 1}`][`colume${k + 1}`].push(rowSeats.shift());
              }
            }
          }
        }
        this.group_columes = group_columes;
        this.one_line_num = cols;
        this.group_num = group_num;
        this.one_colume_num = rows;
        this.seat_table = layout;
        this.hasArrange = true;
      },
      createSeat() {
        if(this.grade_id===''){
          this.vmMsgWarning('请选择年级');
          return;
        }
        if(this.class_id===''){
          this.vmMsgWarning('请选择班级');
          return;
        }
        if(!this.order){
          this.vmMsgWarning('请选择排序依据');
          return;
        }
        if(this.according==='Score'&&!this.exam_id){
          this.vmMsgWarning('选择按成绩排序后请选择考试');
          return;
        }
        if(!this.hasLayout){
          this.vmMsgWarning('该班还没有座位布局');
          return;
        }
        let params = {
          type: 'produceArr',
          order: this.order,
          classId: this.class_id,
          according: this.according,
          sex: this.sex,
          layout: this.layout
        };
        if(this.exam_id){
          params.examId = this.exam_id;
        }
        req.ajaxSend('/school/ClassManage/seatLayout', 'post',params , (res) => {
          if(res.status==1){
            this.students2seats(res.data.map(val=>({name:val.name,id:val.userId})));
          }else{
            this.vmMsgError(res.msg);
          }
          this.editState = 'edited';
        });
      },
      adjustSeat() {
        if(this.grade_id===''){
          this.vmMsgWarning('请选择年级');
          return;
        }
        if(this.class_id===''){
          this.vmMsgWarning('请选择班级');
          return;
        }
        if(!this.hasArrange){
          this.vmMsgWarning('该班还没有座位安排，请先生成座次表');
          return;
        }
//        if (this.allDown) {
        let diff_value_down = this.allDown-this.hasMoveDown;
        this.hasMoveDown = this.allDown;
        for(let i = 0;i<Math.abs(diff_value_down);i++){
          for (let group = 0; group <= this.group_num; group++) {
            for (let i = 0; i < this.group_columes[group]; i++) {
              let colume = this.seat_table[`group${group + 1}`][`colume${i + 1}`];
              if(diff_value_down>0){
                colume.unshift(colume.pop());
              }else{
                colume.push(colume.shift());
              }
            }
          }
        }
        this.student_list = this.studentOrderFromSeat(this.seat_table);
//        }
//        if (this.allRight) {
        let diff_value_right = this.allRight-this.hasMoveRight;
        this.hasMoveRight = this.allRight;
        for(let i = 0;i<Math.abs(diff_value_right);i++){
          let line_col = [];
          for(let i = 0;i<this.one_colume_num;i++){
            let line_stus = this.student_list.splice(0,this.one_line_num);
            if(diff_value_right<0){
              line_stus.push(line_stus.shift());
            }else{
              line_stus.unshift(line_stus.pop());
            }
            line_col = line_col.concat(line_stus);
          }
          this.students2seats(line_col);
        }
//        }
        this.editState = 'edited';
      },
      SeatsliActive(seat) {
        if(!this.selected_seat){
          this.selected_seat = seat;
          this.$set(seat,'selected',true)
        }else{
          if(this.selected_seat.id===seat.id){
            delete seat.selected;
            this.selected_seat=null;
            return;
          }
          let temp_seat = deepCopy(this.selected_seat);
          this.selected_seat.name = seat.name;
          this.selected_seat.id = seat.id;
          this.selected_seat.selected = false;
          seat.name = temp_seat.name;
          seat.id = temp_seat.id;
          delete seat.selected;
          this.selected_seat = null;
          this.student_list = this.studentOrderFromSeat(this.seat_table);
        }
        this.editState = 'edited';
      },
      studentOrderFromSeat(seat_table){
        let arrange = [],
          seats = deepCopy(seat_table);
        for(let k = 0;k<this.one_colume_num;k++){
          for(let i = 0;i<this.group_num;i++){
            for(let j = 0;j<this.group_columes[i];j++){
              arrange.push(seats[`group${i+1}`][`colume${j+1}`].shift());
            }
          }
        }
        return arrange;
      },
      arrangeSave() {
        this.$confirm('是否确定保存该设置?', '提示', {
          confirmButtonText: '确定',
          cancelButtonText: '取消',
          type: 'warning'
        }).then(() => {
          this.save=true;
          let arrange = {},
            seats = deepCopy(this.seat_table);
          let seat_no = 1;
          for(let k = 0;k<this.one_colume_num;k++){
            for(let i = 0;i<this.group_num;i++){
              for(let j = 0;j<this.group_columes[i];j++){
                let student2set = seats[`group${i+1}`][`colume${j+1}`].shift()
                arrange[seat_no++]={
                  name:student2set.name,
                  id:student2set.id
                };
              }
            }
          }
          req.ajaxSend('/school/ClassManage/seatLayout','post',{
            type: 'arrangeSave',
            id: this.layout_data.id,
            arrange
          },(res) =>{
            this.save=false;
            this.editState = 'saved';
            if(res.status===1){
              this.vmMsgSuccess(res.msg);
            }else{
              this.vmMsgError(res.msg);
            }
          })
        }).catch(() => {
          this.editState = 'init';

        });
      },
    },
    beforeRouteLeave (to, from, next) {
      if(this.editState !== 'edited'){
        next();
      }else{
        this.arrangeSave()
      }
    }
  }
  function deepCopy(obj) {
    return JSON.parse(JSON.stringify(obj));
  }
</script>
<style scoped>
  .SeatingArrangements {
    padding: 1.25rem 2rem;
    box-shadow: 0 0.1875rem 0.375rem 0.125rem rgba(0, 0, 0, 0.2);
    border-radius: .5rem;
    margin: 1.25rem 0;
    background-color: #fff;
  }

  @media (max-width: 1920px) {
    .Seats-maintitle {
      margin-left: -12rem;
    }

    .Seat-right {
      margin-left: -5rem;
    }
  }

  @media (max-width: 1600px) {
    .Seats-maintitle {
      margin-left: -6rem;
    }

    .Seat-right {
      margin-left: 0;
    }
  }

  @media (max-width: 1420px) {
    .Seats-maintitle {
      margin-left: -2rem;
    }

    .Seat-right {
      margin-left: 0;
    }
  }

  @media (max-width: 1280px) {
    .Seats-maintitle {
      margin-left: -4rem;
    }

    .Seat-right {
      margin-left: 0;
    }
  }

  .Seats-title {
    background: #4da1ff;
    color: #ffffff;
    width: 4.395rem;
    height: 4.395rem;
    text-align: center;
    cursor: pointer;
    border-radius: .45rem;
  }

  .Seats-title > span {
    display: inline-block;
    padding-top: 1rem;
  }

  .SeatingArrangements .el-input__inner {
    height: 35px;
  }

  .Seats-top {
    width: 37rem;
    height: 2.5rem;
    background: #d2d2d2;
    margin: 2.25rem auto 0 auto;
    border-radius: .5rem;
    line-height: 2.5rem;
    text-align: center;
    font-size: 1rem;
  }

  .Seats-li {
    width: 6.875rem;
    height: 2.5rem;
    border: 1px solid #d2d2d2;
    border-radius: .3rem;
    text-align: center;
    line-height: 2.5rem;
    margin-top: 1rem;
    cursor: pointer;
  }
  .Seats-li.selected {
    border: 1px solid #4da1ff;
    color: #4da1ff;
  }

  .Seats-Btn {
    margin-top: 2.25rem;
    margin-bottom: 0;
    text-align: right;
  }

  .Seats-ul {
    font-size: 1rem;
    display: inline-block;
    margin-left: 1.5rem;
    vertical-align: top;
  }
  .Seats-ul:first-child{
    margin-left: 0;
  }
  .Seats-black{
    text-align: center;
    font-size: 1.5rem;
    color: #999999;
    display: inline-block;
    margin: 0 3rem;
    flex-grow: 0;
    flex-shrink: 0;
  }
  .Seats-black:last-child {
    display: none;
  }

  @media screen and (max-width: 1280px) {
    .Seats-black {
      width: 7.3rem;
    }
  }
  .Seat-wrap{
    display: flex;
    flex-wrap: nowrap;
    align-items: center;
    overflow-x: auto;
  }
  .Seat-content{
    font-size: 0;
    min-width: 9rem;
    margin:2rem auto 0 auto;
    display: inline-block;
    text-align: center;
    vertical-align: middle;
    flex-grow: 0;
    flex-shrink: 0;
  }
</style>
